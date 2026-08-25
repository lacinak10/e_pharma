<?php

namespace App\Support;

use App\Enums\OrderStatus;
use App\Models\CourierReview;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * Construit le menu latéral du back-office et ses compteurs.
 *
 * Les compteurs sont mis en cache très brièvement : la barre est rendue sur
 * chaque écran, mais un back-office reste un flux temps réel.
 */
class BackOfficeNavigation
{
    private const CACHE_SECONDS = 15;

    /**
     * @return array<int, array{label: string, url: string, active: bool, count: int|null, urgent: bool}>
     */
    public function for(User $user): array
    {
        return $user->isCourier() ? $this->courierMenu($user) : $this->managerMenu();
    }

    /**
     * @return array<int, array{label: string, url: string, active: bool, count: int|null, urgent: bool}>
     */
    private function managerMenu(): array
    {
        $counts = $this->managerCounts();

        return [
            $this->item('Tableau de bord', route('admin.dashboard'), 'admin/dashboard'),
            $this->item('Commandes', route('manager.orders.index'), 'admin/orders*', $counts['orders'], urgent: true),
            $this->item('Vérification', route('manager.verifications.index'), 'admin/verifications*', $counts['checking'], urgent: true),
            $this->item('Livraisons', route('manager.deliveries.index'), 'admin/deliveries*', $counts['deliveries']),
            $this->item('Ordonnances', route('manager.prescriptions.index'), 'admin/prescriptions*', $counts['prescriptions']),
            $this->item('Clients', route('manager.customers.index'), 'admin/customers*'),
            $this->item('Médicaments', route('manager.medicines.index'), 'admin/medicines*'),
            $this->item('Pharmacies partenaires', route('manager.pharmacies.index'), 'admin/pharmacies*'),
            $this->item('Livreurs', route('manager.couriers.index'), 'admin/couriers*'),
            $this->item('Avis et notes', route('manager.reviews.index'), 'admin/reviews*', $counts['reviews']),
            $this->item('Statistiques', route('manager.stats.index'), 'admin/stats*'),
            $this->item('Paramètres', route('admin.settings.edit'), 'admin/settings*'),
        ];
    }

    /**
     * @return array<int, array{label: string, url: string, active: bool, count: int|null, urgent: bool}>
     */
    private function courierMenu(User $courier): array
    {
        $counts = $this->courierCounts($courier);

        return [
            $this->item('Mes courses', route('courier.my_orders.index'), 'admin/my-orders', $counts['assigned'], urgent: true),
            $this->item('En cours', route('courier.my_orders.index', ['filter' => 'active']), null, $counts['active']),
            $this->item('Historique', route('courier.my_orders.index', ['filter' => 'done']), null),
            $this->item('Notifications', route('admin.notifications.index'), 'admin/notifications*', $courier->unreadNotifications->count()),
            $this->item('Mon profil', route('admin.profile.edit'), 'admin/profile*'),
            $this->item('Paramètres', route('admin.settings.edit'), 'admin/settings*'),
        ];
    }

    /** @return array<string, int> */
    private function managerCounts(): array
    {
        return Cache::remember('bo.nav.manager', self::CACHE_SECONDS, fn () => [
            'orders'        => Order::awaitingManager()->count(),
            'checking'      => Order::beingChecked()->count(),
            'deliveries'    => Order::inDelivery()->count(),
            'prescriptions' => Order::where('has_prescription', true)
                ->withStatus([OrderStatus::PENDING_VALIDATION, OrderStatus::CHECKING])
                ->count(),
            'reviews'       => CourierReview::whereDate('created_at', '>=', now()->subDay())->count(),
        ]);
    }

    /** @return array<string, int> */
    private function courierCounts(User $courier): array
    {
        return Cache::remember("bo.nav.courier.{$courier->id}", self::CACHE_SECONDS, fn () => [
            'assigned' => Order::withStatus([OrderStatus::COURIER_ASSIGNED])
                ->whereHas('assignment', fn ($q) => $q->where('courier_id', $courier->id))
                ->count(),
            'active'   => Order::inDelivery()
                ->whereHas('assignment', fn ($q) => $q->where('courier_id', $courier->id))
                ->count(),
        ]);
    }

    /**
     * @return array{label: string, url: string, active: bool, count: int|null, urgent: bool}
     */
    private function item(string $label, string $url, ?string $pattern = null, ?int $count = null, bool $urgent = false): array
    {
        return [
            'label'  => $label,
            'url'    => $url,
            'active' => $pattern !== null && request()->is($pattern),
            'count'  => $count > 0 ? $count : null,
            'urgent' => $urgent,
        ];
    }
}
