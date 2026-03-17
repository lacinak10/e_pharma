<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Notification;

class NewAssignmentNotification extends Notification
{
    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'    => 'delivery',
            'title'   => 'Nouvelle affectation',
            'message' => "Vous avez été affecté à la commande #{$this->order->id}.",
            'url'     => route('courier.my_orders.show', $this->order),
            'icon'    => 'fa-truck',
            'color'   => 'indigo',
        ];
    }
}
