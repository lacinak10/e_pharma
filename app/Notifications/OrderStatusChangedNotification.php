<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Notification;

class OrderStatusChangedNotification extends Notification
{
    public function __construct(public Order $order, public string $status) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        [$title, $message, $icon, $color] = match ($this->status) {
            'accepted'    => ['Livraison acceptée',   "Le livreur a accepté la commande #{$this->order->id}.",            'fa-circle-check', 'green'],
            'refused'     => ['Livraison refusée',    "Le livreur a refusé la commande #{$this->order->id}.",            'fa-circle-xmark', 'red'],
            'in_delivery' => ['Livraison en cours',   "La commande #{$this->order->id} est en cours de livraison.",      'fa-truck',        'indigo'],
            'delivered'   => ['Commande livrée',      "La commande #{$this->order->id} a été livrée avec succès.",        'fa-box-open',     'green'],
            default       => ['Statut mis à jour',    "La commande #{$this->order->id} a changé de statut.",             'fa-circle-info',  'blue'],
        };

        return [
            'type'    => 'delivery',
            'title'   => $title,
            'message' => $message,
            'url'     => route('manager.orders.show', $this->order),
            'icon'    => $icon,
            'color'   => $color,
        ];
    }
}
