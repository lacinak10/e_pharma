<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Notification;

class NewOrderNotification extends Notification
{
    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'    => 'order',
            'title'   => 'Nouvelle commande',
            'message' => "Commande #{$this->order->id} reçue de {$this->order->user->name}.",
            'url'     => route('manager.orders.show', $this->order),
            'icon'    => 'fa-bag-shopping',
            'color'   => 'blue',
        ];
    }
}
