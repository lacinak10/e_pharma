<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Trace d'un webhook déjà traité.
 *
 * L'unicité de `event_id` est le verrou de déduplication : GeniusPay rejoue un
 * événement tant qu'il n'a pas reçu de 2xx, et un « payment.success » rejoué
 * ne doit ni recréditer la commande ni dupliquer sa timeline.
 */
class PaymentWebhookEvent extends Model
{
    public $timestamps = false;

    protected $fillable = ['event_id', 'event', 'reference', 'received_at'];

    protected $casts = ['received_at' => 'datetime'];
}
