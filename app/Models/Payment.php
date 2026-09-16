<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Une tentative de paiement en ligne auprès de GeniusPay.
 *
 * Une commande peut en porter plusieurs : un lien expiré ou un échec
 * d'opérateur laisse sa trace et le client en génère un nouveau.
 */
class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'provider',
        'reference',
        'status',
        'amount',
        'currency',
        'method',
        'environment',
        'checkout_url',
        'expires_at',
        'paid_at',
        'failure_reason',
        'last_payload',
    ];

    protected $casts = [
        'status'       => PaymentStatus::class,
        'amount'       => 'integer',
        'expires_at'   => 'datetime',
        'paid_at'      => 'datetime',
        'last_payload' => 'array',
    ];

    // ── Relations ────────────────────────────────────────────────────────

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // ── Scopes ───────────────────────────────────────────────────────────

    /** Paiements partis dont le sort n'est pas connu — cible de la réconciliation. */
    public function scopeAwaiting(Builder $query): Builder
    {
        return $query->whereIn('status', [
            PaymentStatus::PENDING->value,
            PaymentStatus::PROCESSING->value,
        ]);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', PaymentStatus::COMPLETED->value);
    }

    // ── Présentation ─────────────────────────────────────────────────────

    /**
     * « Wave », « Orange Money »… GeniusPay ajoute des opérateurs sans
     * prévenir : tout code inconnu est rendu tel quel plutôt que masqué.
     */
    public function getMethodLabelAttribute(): string
    {
        return match ($this->method) {
            'wave'          => 'Wave',
            'orange_money'  => 'Orange Money',
            'mtn_money'     => 'MTN MoMo',
            'moov_money'    => 'Moov Money',
            'airtel_money'  => 'Airtel Money',
            'card'          => 'Carte bancaire',
            'pawapay'       => 'Mobile Money',
            'paystack'      => 'Carte bancaire',
            'mobile_money'  => 'Mobile Money',
            null, ''        => 'Non précisé',
            default         => ucfirst(str_replace('_', ' ', $this->method)),
        };
    }

    /** Le lien de checkout est-il encore utilisable ? (24 h côté GeniusPay) */
    public function isUsable(): bool
    {
        return $this->status->isAwaiting()
            && $this->checkout_url !== null
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }

    public function isCompleted(): bool
    {
        return $this->status === PaymentStatus::COMPLETED;
    }
}
