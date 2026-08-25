<?php

namespace App\Notifications;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Notifications\Notification;

/**
 * Prévient le client à chaque étape du cycle de vie de sa commande.
 * Les textes reprennent mot pour mot ceux du système de design.
 */
class OrderProgressNotification extends Notification
{
    public function __construct(public Order $order)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $status  = $this->order->status;
        $courier = $this->order->assignment?->courier;

        $message = match ($status) {
            OrderStatus::PENDING_VALIDATION => 'Votre commande a été prise en charge et est en attente de validation par le manager.',
            OrderStatus::CHECKING           => 'Vos médicaments sont en cours de vérification auprès de nos pharmacies partenaires afin de confirmer leur disponibilité. Vous serez informé du résultat dans moins de 5 minutes.',
            OrderStatus::AVAILABLE          => 'Vos médicaments sont disponibles : votre commande est validée.',
            OrderStatus::PARTIALLY_AVAILABLE => 'Une partie seulement de votre commande est disponible. Indiquez-nous comment vous souhaitez continuer.',
            OrderStatus::UNAVAILABLE        => 'Vos médicaments sont introuvables chez nos partenaires ce soir. Des alternatives vous sont proposées.',
            OrderStatus::REFUSED            => 'Votre commande a été refusée' . ($this->order->refusal_reason ? " : {$this->order->refusal_reason}" : '.'),
            OrderStatus::COURIER_ASSIGNED   => $courier
                ? "{$courier->short_name} ({$courier->phone}) prend en charge votre livraison. Arrivée estimée dans {$this->order->eta_minutes} minutes."
                : 'Un livreur a été assigné à votre commande.',
            OrderStatus::TO_PHARMACY        => 'Le livreur est en route vers la pharmacie.',
            OrderStatus::AT_PHARMACY        => 'Le livreur est arrivé à la pharmacie.',
            OrderStatus::PICKED_UP          => 'Le médicament a été récupéré.',
            OrderStatus::TO_CLIENT          => 'Le livreur est en route vers vous.',
            OrderStatus::DELIVERED          => 'Votre commande a été livrée. Vous pouvez maintenant noter votre livreur.',
            OrderStatus::CANCELED           => 'Votre commande a été annulée' . ($this->order->cancel_reason ? " : {$this->order->cancel_reason}" : '.'),
            OrderStatus::RATED              => 'Merci pour votre note.',
        };

        return [
            'type'     => 'order',
            'title'    => $status->badge(),
            'message'  => $message,
            'url'      => route('store.orders.show', $this->order),
            'icon'     => $this->icon($status),
            'color'    => $this->color($status),
            'order_id' => $this->order->id,
            'status'   => $status->value,
        ];
    }

    private function icon(OrderStatus $status): string
    {
        return match (true) {
            $status === OrderStatus::CHECKING       => 'fa-hourglass-half',
            $status === OrderStatus::AVAILABLE      => 'fa-circle-check',
            $status === OrderStatus::UNAVAILABLE,
            $status === OrderStatus::REFUSED        => 'fa-circle-xmark',
            $status === OrderStatus::DELIVERED      => 'fa-box-open',
            $status === OrderStatus::CANCELED       => 'fa-ban',
            $status->isCourierPhase()               => 'fa-motorcycle',
            default                                 => 'fa-circle-info',
        };
    }

    /** Couleur reconnue par le composant de notification du back-office. */
    private function color(OrderStatus $status): string
    {
        return match ($status->tone()) {
            'green' => 'green',
            'amber' => 'yellow',
            'red'   => 'red',
            'blue'  => 'indigo',
            default => 'gray',
        };
    }
}
