<?php

namespace App\Models;

use App\Enums\ItemAvailability;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicine extends Model
{
    /** Packshots libres de droit servant de repli quand aucune image n'est renseignée. */
    private const FALLBACK_IMAGES = [
        'medicaments.jpg', 'blister.jpg', 'comprimes.jpg', 'gelules.jpg',
    ];

    /** Fenêtre glissante sur laquelle on résume les vérifications passées. */
    public const SIGNAL_WINDOW_DAYS = 7;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'indication',
        'pack',
        'dosage',
        'requires_prescription',
        'price',
        'reference',
        'image_url',
        'is_active',
    ];

    protected $casts = [
        'price'                 => 'integer',
        'is_active'             => 'boolean',
        'requires_prescription' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Image du produit : le visuel renseigné, sinon un packshot libre de droit
     * choisi de façon déterministe pour que la fiche reste stable.
     */
    public function getImageSrcAttribute(): string
    {
        if (! empty($this->image_url)) {
            return str_starts_with($this->image_url, 'http')
                ? $this->image_url
                : asset('storage/' . $this->image_url);
        }

        $file = self::FALLBACK_IMAGES[$this->id % count(self::FALLBACK_IMAGES)];

        return asset("assets/images/photos/{$file}");
    }

    /**
     * ePharma ne détient aucun stock : la disponibilité réelle n'est connue
     * qu'au moment où le manager appelle les pharmacies partenaires, pour une
     * commande donnée. Ce que l'on peut afficher honnêtement, c'est ce qui a
     * été *constaté* lors des vérifications récentes.
     *
     * Le scope ajoute trois indicateurs sur une fenêtre glissante :
     *  - available_partners : pharmacies distinctes ayant confirmé le produit
     *  - checks_count       : nombre de vérifications effectuées
     *  - refusals_count     : vérifications négatives
     */
    public function scopeWithAvailabilitySignal(Builder $query, int $days = self::SIGNAL_WINDOW_DAYS): Builder
    {
        $since = now()->subDays($days);

        $sub = fn (callable $filter) => OrderItem::query()
            ->whereColumn('order_items.medicine_id', 'medicines.id')
            ->where('order_items.checked_at', '>=', $since)
            ->tap($filter);

        return $query
            ->select('medicines.*')
            ->addSelect([
                'available_partners' => $sub(
                    fn (Builder $q) => $q->where('availability', ItemAvailability::AVAILABLE->value)
                        ->whereNotNull('pharmacy_id')
                )->selectRaw('COUNT(DISTINCT pharmacy_id)'),

                'checks_count' => $sub(fn (Builder $q) => $q)->selectRaw('COUNT(*)'),

                'refusals_count' => $sub(
                    fn (Builder $q) => $q->where('availability', ItemAvailability::UNAVAILABLE->value)
                )->selectRaw('COUNT(*)'),
            ]);
    }

    /**
     * Signal affiché sur la carte produit. Jamais une promesse de stock :
     * une observation datée, ou l'aveu qu'on ne sait pas encore.
     *
     * @return array{label: string, color: string, tone: string}
     */
    public function availabilitySignal(): array
    {
        $partners = (int) ($this->available_partners ?? 0);
        $checks   = (int) ($this->checks_count ?? 0);
        $refusals = (int) ($this->refusals_count ?? 0);

        if ($partners > 0) {
            return [
                'label' => "Confirmé chez {$partners} partenaire" . ($partners > 1 ? 's' : ''),
                'color' => '#0E5C43',
                'tone'  => 'green',
            ];
        }

        if ($checks > 0 && $refusals === $checks) {
            return ['label' => 'Rarement trouvé ces derniers jours', 'color' => '#A6382F', 'tone' => 'red'];
        }

        return ['label' => 'Disponibilité à vérifier', 'color' => '#B87514', 'tone' => 'amber'];
    }

    public function availabilityLabel(): string
    {
        return $this->availabilitySignal()['label'];
    }

    public function availabilityColor(): string
    {
        return $this->availabilitySignal()['color'];
    }

    /** Lignes de commande référençant ce médicament — base du signal de disponibilité. */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** « 1000 mg · 8 comprimés » */
    public function getPackLabelAttribute(): string
    {
        return collect([$this->dosage, $this->pack])->filter()->implode(' · ') ?: '—';
    }
}
