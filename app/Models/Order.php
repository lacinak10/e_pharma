<?php

namespace App\Models;

use App\Enums\AssignmentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    /** Durée annoncée au client pour la vérification de disponibilité. */
    public const CHECK_DURATION_SECONDS = 300;

    protected $fillable = [
        'reference',
        'user_id',
        'pharmacy_id',
        'status',
        'validated_at',
        'checking_started_at',
        'check_deadline_at',
        'verdict_at',
        'assigned_at',
        'delivery_address',
        'delivery_phone',
        'notes',
        'refusal_reason',
        'cancel_reason',
        'canceled_by',
        'payment_method',
        'total_amount',
        'delivery_fee',
        'subtotal',
        'eta_minutes',
        'delivery_code',
        'canceled_at',
        'delivered_at',
        'prescription_path',
        'has_prescription',
        'prescription_scope',
        'prescription_comment',
    ];

    protected $casts = [
        'status'              => OrderStatus::class,
        'total_amount'        => 'integer',
        'subtotal'            => 'integer',
        'delivery_fee'        => 'integer',
        'eta_minutes'         => 'integer',
        'validated_at'        => 'datetime',
        'checking_started_at' => 'datetime',
        'check_deadline_at'   => 'datetime',
        'verdict_at'          => 'datetime',
        'assigned_at'         => 'datetime',
        'canceled_at'         => 'datetime',
        'delivered_at'        => 'datetime',
        'has_prescription'    => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order): void {
            $order->reference ??= static::nextReference();
        });
    }

    /**
     * « CMD-20260904-0001 » — séquence remise à zéro chaque jour.
     *
     * Le LIKE préfixé s'appuie sur l'index unique de `reference`. Le
     * lockForUpdate sérialise deux commandes nées dans la même seconde ;
     * l'index unique reste le garde-fou si l'appel a lieu hors transaction.
     */
    public static function nextReference(?\DateTimeInterface $date = null): string
    {
        $prefix = 'CMD-' . ($date ? Carbon::instance($date) : now())->format('Ymd') . '-';

        $last = static::query()
            ->where('reference', 'like', $prefix . '%')
            ->lockForUpdate()
            ->max('reference');

        $sequence = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    // ── Relations ────────────────────────────────────────────────────────

    // Les comptes supprimés le sont en douceur : une commande archivée doit
    // continuer d'afficher qui l'a passée et qui l'a traitée.
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    /** Alias historique de client(). */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function assignment(): HasOne
    {
        return $this->hasOne(DeliveryAssignment::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(OrderEvent::class)->latest();
    }

    public function review(): HasOne
    {
        return $this->hasOne(CourierReview::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest('id');
    }

    /** La tentative de paiement en cours — la plus récente fait foi. */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function canceledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'canceled_by')->withTrashed();
    }

    // ── Scopes ───────────────────────────────────────────────────────────

    /** @param  array<int, OrderStatus>  $statuses */
    public function scopeWithStatus(Builder $query, array $statuses): Builder
    {
        return $query->whereIn('status', array_map(fn (OrderStatus $s) => $s->value, $statuses));
    }

    public function scopeAwaitingManager(Builder $query): Builder
    {
        return $query->withStatus([OrderStatus::PENDING_VALIDATION]);
    }

    public function scopeBeingChecked(Builder $query): Builder
    {
        return $query->withStatus([OrderStatus::CHECKING]);
    }

    /**
     * Commandes validées, en attente d'un livreur.
     *
     * Une course refusée par son livreur laisse derrière elle une attribution
     * refusée : la commande doit revenir dans cette file, sans quoi elle
     * n'aurait plus aucun moyen d'être réattribuée.
     */
    public function scopeAwaitingCourier(Builder $query): Builder
    {
        return $query->withStatus([OrderStatus::AVAILABLE, OrderStatus::PARTIALLY_AVAILABLE])
            ->whereDoesntHave(
                'assignment',
                fn (Builder $q) => $q->where('status', '!=', AssignmentStatus::REFUSED->value)
            );
    }

    public function scopeInDelivery(Builder $query): Builder
    {
        return $query->withStatus([
            OrderStatus::COURIER_ASSIGNED, OrderStatus::TO_PHARMACY,
            OrderStatus::AT_PHARMACY, OrderStatus::PICKED_UP, OrderStatus::TO_CLIENT,
        ]);
    }

    // ── Le chrono de 5 minutes ───────────────────────────────────────────

    /** Secondes restantes avant l'échéance annoncée au client. */
    public function secondsLeftForCheck(): int
    {
        if (! $this->check_deadline_at) {
            return 0;
        }

        // Carbon 3 renvoie un float : on tronque avant de comparer.
        return max(0, (int) now()->diffInSeconds($this->check_deadline_at, false));
    }

    /** « 4:12 » — format du compte à rebours. */
    public function getCheckClockAttribute(): string
    {
        $left = $this->secondsLeftForCheck();

        return intdiv($left, 60) . ':' . str_pad((string) ($left % 60), 2, '0', STR_PAD_LEFT);
    }

    /** Progression de la barre du chrono, en pourcentage restant. */
    public function getCheckProgressAttribute(): int
    {
        return (int) round($this->secondsLeftForCheck() / self::CHECK_DURATION_SECONDS * 100);
    }

    /** Le chrono est dépassé alors que le verdict n'est pas rendu. */
    public function isCheckOverdue(): bool
    {
        return $this->status === OrderStatus::CHECKING && $this->secondsLeftForCheck() === 0;
    }

    // ── Présentation ─────────────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return $this->status->label();
    }

    public function getStatusBadgeAttribute(): string
    {
        return $this->status->badge();
    }

    /** Résumé des articles : « Amoxicilline, sirop antitussif ». */
    public function getItemsSummaryAttribute(): string
    {
        return $this->items->pluck('medicine_name')->filter()->implode(', ') ?: '—';
    }

    /** « Espèces à la livraison » — ce que le livreur doit encaisser, ou non. */
    public function getPaymentLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'cash'  => 'Espèces à la livraison',
            'momo'  => 'Mobile Money',
            'card'  => 'Carte bancaire',
            default => 'Non précisé',
        };
    }

    /** Le livreur repart-il avec de l'argent liquide à encaisser ? */
    public function collectsCash(): bool
    {
        return $this->payment_method === 'cash';
    }

    /**
     * La commande se règle en ligne avant livraison.
     *
     * Le paiement n'est demandé qu'après le verdict : avant lui, `total_amount`
     * est indicatif et serait faussé par toute ligne finalement indisponible.
     */
    public function requiresPrepayment(): bool
    {
        return in_array($this->payment_method, ['momo', 'card'], true);
    }

    /** Le montant est encaissé chez GeniusPay. */
    public function isPaid(): bool
    {
        return $this->payments()->completed()->exists();
    }

    /** La commande attend un règlement en ligne pour pouvoir partir. */
    public function awaitsPayment(): bool
    {
        return $this->requiresPrepayment() && ! $this->isPaid();
    }

    /** Statut du paiement en cours, ou null si aucun n'a encore été demandé. */
    public function paymentStatus(): ?PaymentStatus
    {
        return $this->payment?->status;
    }

    /**
     * Le panier n'a pas encore été composé.
     *
     * Une commande sur ordonnance naît sans aucune ligne : afficher
     * « sous-total 0 F » y présenterait un montant inconnu comme un montant nul.
     */
    public function awaitsComposition(): bool
    {
        return $this->relationLoaded('items')
            ? $this->items->isEmpty()
            : ! $this->items()->exists();
    }

    /** Le client a-t-il déjà noté son livreur ? */
    public function isRated(): bool
    {
        return $this->status === OrderStatus::RATED || $this->review()->exists();
    }

    /** La commande est livrée et attend encore une note. */
    public function awaitsReview(): bool
    {
        return $this->status === OrderStatus::DELIVERED && ! $this->review()->exists();
    }

    /**
     * Les 5 étapes de livraison, prêtes à afficher dans la timeline.
     *
     * @return array<int, array{label: string, sub: string, status: OrderStatus, state: string, time: string}>
     */
    public function deliveryTimeline(): array
    {
        $current = $this->status->deliveryStep() ?? 0;

        // Une commande livrée a terminé sa dernière étape : elle ne doit pas
        // rester affichée « en cours » face à un badge « Livrée ».
        $completed = in_array($this->status, [OrderStatus::DELIVERED, OrderStatus::RATED], true);

        $steps = [
            [OrderStatus::TO_PHARMACY, 'En route vers la pharmacie', $this->pharmacy?->name ?? 'Pharmacie partenaire'],
            [OrderStatus::AT_PHARMACY, 'Arrivé à la pharmacie', 'Vérification du panier'],
            [OrderStatus::PICKED_UP, 'Médicament récupéré', $this->items->count() . ' article(s)'],
            [OrderStatus::TO_CLIENT, 'En route vers vous', $this->eta_minutes ? "Arrivée estimée dans {$this->eta_minutes} min" : 'En chemin'],
            [
                OrderStatus::DELIVERED,
                'Commande livrée',
                $completed
                    ? 'Remise confirmée' . ($this->delivered_at ? ' à ' . $this->delivered_at->format('H:i') : '')
                    : 'Code de confirmation à donner',
            ],
        ];

        $times = $this->events
            ->sortBy('created_at')
            ->groupBy(fn (OrderEvent $e) => $e->status->value)
            ->map(fn ($group) => $group->first()->created_at->format('H:i'));

        return collect($steps)->map(function (array $step, int $i) use ($current, $times, $completed) {
            [$status, $label, $sub] = $step;
            $index = $i + 1;

            $state = match (true) {
                $index < $current   => 'done',
                $index === $current => $completed ? 'done' : 'current',
                default             => 'todo',
            };

            return [
                'status' => $status,
                'label'  => $label,
                'sub'    => $sub,
                'state'  => $state,
                'time'   => $times[$status->value] ?? ($state === 'current' ? 'en cours' : '—'),
            ];
        })->all();
    }
}
