<?php

namespace App\Services;

use App\Models\Medicine;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Support\Facades\Notification;

class StockService
{
    /**
     * Calcule le statut stock d'un médicament.
     */
    public function computeStatus(int $stock, int $alertThreshold): string
    {
        if ($stock <= 0) {
            return 'Épuisé';
        }

        if ($stock <= $alertThreshold) {
            return 'Stock faible';
        }

        return 'En stock';
    }

    /**
     * Applique le statut calculé sur un médicament et sauvegarde.
     */
    public function updateStatus(Medicine $medicine): void
    {
        $oldStatus = $medicine->status;
        $newStatus = $this->computeStatus($medicine->stock, $medicine->alert_threshold);

        $medicine->status = $newStatus;
        $medicine->save();

        // Notifier les managers si le stock devient faible ou épuisé
        if ($oldStatus === 'En stock' && in_array($newStatus, ['Stock faible', 'Épuisé'])) {
            $managers = User::where('role', User::ROLE_MANAGER)->get();
            Notification::send($managers, new LowStockNotification($medicine));
        }
    }

    /**
     * Restaure le stock d'une commande annulée.
     */
    public function restoreOrderStock(\App\Models\Order $order): void
    {
        foreach ($order->items as $item) {
            $medicine = $item->medicine;
            if ($medicine) {
                $medicine->increment('stock', $item->quantity);
                $this->updateStatus($medicine);
            }
        }
    }
}
