<?php

namespace App\View\Composers;

use App\Enums\OrderStatus;
use App\Models\CourierReview;
use App\Models\Order;
use App\Models\Pharmacy;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * Contexte partagé par toute la boutique : panier, commande en cours et
 * preuve sociale de la barre utilitaire.
 */
class StoreComposer
{
    public function compose(View $view): void
    {
        $view->with([
            'cartCount'     => $this->cartCount(),
            'currentOrder'  => $this->currentOrder(),
            'storeRating'   => $this->rating(),
            'partnerCount'  => Cache::remember('store.partners.count', 600, fn () => Pharmacy::active()->count()),
        ]);
    }

    private function cartCount(): int
    {
        return collect(session('cart', []))->sum(fn (array $line) => (int) ($line['qty'] ?? 0));
    }

    /** La commande à suivre, affichée dans le bandeau persistant. */
    private function currentOrder(): ?Order
    {
        if (! auth()->check() || ! auth()->user()->isClient()) {
            return null;
        }

        return Order::where('user_id', auth()->id())
            ->withStatus([
                OrderStatus::PENDING_VALIDATION, OrderStatus::CHECKING,
                OrderStatus::AVAILABLE, OrderStatus::PARTIALLY_AVAILABLE,
                OrderStatus::COURIER_ASSIGNED, OrderStatus::TO_PHARMACY,
                OrderStatus::AT_PHARMACY, OrderStatus::PICKED_UP, OrderStatus::TO_CLIENT,
            ])
            ->latest()
            ->first();
    }

    /** @return array{average: float, count: int} */
    private function rating(): array
    {
        return Cache::remember('store.rating', 600, fn () => [
            'average' => round((float) CourierReview::avg('rating'), 1),
            'count'   => CourierReview::count(),
        ]);
    }
}
