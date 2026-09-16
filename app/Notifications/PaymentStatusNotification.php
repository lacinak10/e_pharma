<?php

namespace App\Notifications;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Notifications\Notification;

/**
 * Prévient le client du sort de son paiement en ligne.
 *
 * Distincte d'OrderProgressNotification, qui tire son message du statut de la
 * commande : un paiement ne fait pas avancer le cycle logistique, il le
 * débloque. Réutiliser l'autre notification aurait répété « vos médicaments
 * sont disponibles » au moment d'annoncer un encaissement.
 */
class PaymentStatusNotification extends Notification
{
    public function __construct(public Payment $payment)
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
        $status = $this->payment->status;
        $order  = $this->payment->order;
        $amount = number_format($this->payment->amount, 0, ',', ' ');

        $message = match ($status) {
            PaymentStatus::COMPLETED => "Paiement de {$amount} FCFA confirmé par {$this->payment->method_label}. Votre commande part en livraison.",
            PaymentStatus::REFUNDED  => "Le montant de {$amount} FCFA vous a été remboursé.",
            default                  => $status->label(),
        };

        return [
            'type'     => 'payment',
            'title'    => $status->badge(),
            'message'  => $message,
            'url'      => route('store.orders.show', $order),
            'icon'     => $this->icon($status),
            'color'    => $this->color($status),
            'order_id' => $order->id,
            'status'   => $status->value,
        ];
    }

    private function icon(PaymentStatus $status): string
    {
        return match ($status) {
            PaymentStatus::COMPLETED => 'fa-circle-check',
            PaymentStatus::REFUNDED  => 'fa-rotate-left',
            PaymentStatus::FAILED,
            PaymentStatus::EXPIRED   => 'fa-circle-xmark',
            PaymentStatus::CANCELLED => 'fa-ban',
            default                  => 'fa-credit-card',
        };
    }

    /** Couleur reconnue par le composant de notification du back-office. */
    private function color(PaymentStatus $status): string
    {
        return match ($status->tone()) {
            'green' => 'green',
            'amber' => 'yellow',
            'red'   => 'red',
            default => 'gray',
        };
    }
}
