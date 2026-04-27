<?php

namespace App\Notifications;

use App\Models\Medicine;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    public function __construct(public Medicine $medicine) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $label = $this->medicine->stock <= 0 ? 'Épuisé' : 'Stock faible';
        $icon  = $this->medicine->stock <= 0 ? 'fa-ban'  : 'fa-triangle-exclamation';
        $color = $this->medicine->stock <= 0 ? 'red'     : 'yellow';

        return [
            'type'    => 'stock',
            'title'   => $label . ' — ' . $this->medicine->name,
            'message' => "{$this->medicine->name} : {$this->medicine->stock} unité(s) restante(s).",
            'url'     => route('manager.stock.edit', $this->medicine),
            'icon'    => $icon,
            'color'   => $color,
        ];
    }
}
