<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    // La colonne deleted_at existe depuis la migration de janvier : un livreur
    // supprimé doit disparaître des listes sans effacer l'historique de ses courses.
    use HasFactory, Notifiable, SoftDeletes;

    public const ROLE_CLIENT  = 'client';
    public const ROLE_MANAGER = 'manager';
    public const ROLE_COURIER = 'courier';

    /** Nombre de portraits libres de droit embarqués dans public/assets/images/avatars. */
    private const AVATAR_COUNT = 8;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'prenom',
        'email',
        'password',
        'role',
        'phone',
        'zone',
        'latitude',
        'longitude',
        'avatar_path',
        'is_available',
        'is_active',
    ];

    /**
     * Un compte est actif tant qu'on ne l'a pas explicitement désactivé.
     * Aligné sur les valeurs par défaut de la table.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_active'    => true,
        'is_available' => true,
    ];

    /**
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'latitude'          => 'float',
        'longitude'         => 'float',
        'is_active'         => 'boolean',
        'is_available'      => 'boolean',
    ];

    // ── Rôles ────────────────────────────────────────────────────────────

    public function isClient(): bool
    {
        return $this->role === self::ROLE_CLIENT;
    }

    public function isManager(): bool
    {
        return $this->role === self::ROLE_MANAGER;
    }

    public function isCourier(): bool
    {
        return $this->role === self::ROLE_COURIER;
    }

    /**
     * Le compte a-t-il été explicitement désactivé ?
     *
     * On ne bloque que sur un « false » assumé : une instance dont la colonne
     * n'a pas été chargée ne doit pas être prise pour un compte suspendu.
     */
    public function isDeactivated(): bool
    {
        return $this->getAttribute('is_active') !== null && ! $this->is_active;
    }

    // ── Relations ────────────────────────────────────────────────────────

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    public function courierAssignments(): HasMany
    {
        return $this->hasMany(DeliveryAssignment::class, 'courier_id');
    }

    public function managerAssignments(): HasMany
    {
        return $this->hasMany(DeliveryAssignment::class, 'assigned_by');
    }

    /** Avis reçus en tant que livreur. */
    public function reviews(): HasMany
    {
        return $this->hasMany(CourierReview::class, 'courier_id');
    }

    /** Avis laissés en tant que client. */
    public function writtenReviews(): HasMany
    {
        return $this->hasMany(CourierReview::class, 'client_id');
    }

    // ── Scopes ───────────────────────────────────────────────────────────

    public function scopeCouriers(Builder $query): Builder
    {
        return $query->where('role', self::ROLE_COURIER)->where('is_active', true);
    }

    public function scopeClients(Builder $query): Builder
    {
        return $query->where('role', self::ROLE_CLIENT);
    }

    // ── Présentation ─────────────────────────────────────────────────────

    /** « Mamadou K. » — forme courte utilisée partout dans l'interface. */
    public function getShortNameAttribute(): string
    {
        $parts = preg_split('/\s+/', trim($this->name)) ?: [];

        if (count($parts) < 2) {
            return $this->name;
        }

        $last = array_pop($parts);

        return implode(' ', $parts) . ' ' . mb_strtoupper(mb_substr($last, 0, 1)) . '.';
    }

    /**
     * Portrait de l'utilisateur : le fichier téléversé s'il existe, sinon l'un
     * des portraits libres de droit embarqués, choisi de façon déterministe.
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar_path) {
            return str_starts_with($this->avatar_path, 'http')
                ? $this->avatar_path
                : asset('storage/' . $this->avatar_path);
        }

        $index = ($this->id % self::AVATAR_COUNT) + 1;

        return asset("assets/images/avatars/av-{$index}.jpg");
    }

    // ── Métier livreur ───────────────────────────────────────────────────

    /** Note moyenne sur 5, arrondie au dixième. */
    public function getRatingAttribute(): ?float
    {
        $average = $this->reviews_avg_rating ?? $this->reviews()->avg('rating');

        return $average === null ? null : round((float) $average, 1);
    }

    /** « ★★★★★ » d'après la note moyenne. */
    public function getStarsAttribute(): string
    {
        $filled = (int) round($this->rating ?? 0);

        return str_repeat('★', $filled) . str_repeat('☆', 5 - $filled);
    }

    /** Nombre de courses en cours pour ce livreur. */
    public function activeDeliveriesCount(): int
    {
        return $this->courierAssignments()
            ->whereHas('order', fn (Builder $q) => $q->inDelivery())
            ->count();
    }

    /**
     * Distance à vol d'oiseau en kilomètres, ou null si l'une des positions manque.
     * Formule de haversine — suffisante pour classer des livreurs dans une même ville.
     */
    public function distanceToKm(?float $latitude, ?float $longitude): ?float
    {
        if ($latitude === null || $longitude === null || $this->latitude === null || $this->longitude === null) {
            return null;
        }

        $earthRadius = 6371;
        $dLat = deg2rad($latitude - $this->latitude);
        $dLon = deg2rad($longitude - $this->longitude);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($this->latitude)) * cos(deg2rad($latitude)) * sin($dLon / 2) ** 2;

        return round($earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a)), 1);
    }

    /** État affiché dans le tableau d'attribution du manager. */
    public function courierState(): string
    {
        if (! $this->is_available) {
            return 'Indisponible';
        }

        $current = $this->courierAssignments()
            ->with('order:id,status')
            ->latest()
            ->first()?->order?->status;

        return match ($current) {
            OrderStatus::AT_PHARMACY, OrderStatus::TO_PHARMACY => 'En pharmacie',
            OrderStatus::PICKED_UP, OrderStatus::TO_CLIENT     => 'En livraison',
            default                                            => 'Disponible',
        };
    }
}
