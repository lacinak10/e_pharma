<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Un événement du cycle de vie d'une commande. Alimente la timeline client
 * et le flux « Activité en direct » du back-office.
 */
class OrderEvent extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'user_id', 'status', 'message', 'meta'];

    protected $casts = [
        'status' => OrderStatus::class,
        'meta'   => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** Auteur de l'événement (manager, livreur ou client). */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    /** Étiquette technique du flux temps réel : EN_ROUTE_PHARMACIE, DISPONIBLE… */
    public function getTagAttribute(): string
    {
        return match ($this->status) {
            OrderStatus::PENDING_VALIDATION  => 'EN_ATTENTE_VALIDATION',
            OrderStatus::REFUSED             => 'REFUSEE',
            OrderStatus::CHECKING            => 'VERIFICATION',
            OrderStatus::AVAILABLE           => 'DISPONIBLE',
            OrderStatus::PARTIALLY_AVAILABLE => 'PARTIELLEMENT_DISPO',
            OrderStatus::UNAVAILABLE         => 'INDISPONIBLE',
            OrderStatus::COURIER_ASSIGNED    => 'LIVREUR_ASSIGNE',
            OrderStatus::TO_PHARMACY         => 'EN_ROUTE_PHARMACIE',
            OrderStatus::AT_PHARMACY         => 'ARRIVE_PHARMACIE',
            OrderStatus::PICKED_UP           => 'MEDICAMENT_RECUPERE',
            OrderStatus::TO_CLIENT           => 'EN_ROUTE_CLIENT',
            OrderStatus::DELIVERED           => 'LIVREE',
            OrderStatus::RATED               => 'NOTEE',
            OrderStatus::CANCELED            => 'ANNULEE',
        };
    }

    public function getColorAttribute(): string
    {
        return $this->status->color();
    }
}
