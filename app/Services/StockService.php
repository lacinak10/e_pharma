<?php

namespace App\Services;

use App\Models\Medicine;

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
        $medicine->status = $this->computeStatus($medicine->stock, $medicine->alert_threshold);
        $medicine->save();
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
