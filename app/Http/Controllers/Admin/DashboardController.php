<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\CourierReview;
use App\Models\Order;
use App\Models\OrderEvent;
use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Poste de pilotage du manager : ce qui attend une décision, ce qui roule,
 * et ce qui vient de se passer.
 */
class DashboardController extends Controller
{
    /** Au-delà de ce reste, une commande n'est plus « proche de l'échéance ». */
    private const ALERT_THRESHOLD_SECONDS = 120;

    public function index(): View|RedirectResponse
    {
        $user = auth()->user();

        if ($user->isCourier()) {
            return redirect()->route('courier.my_orders.index');
        }

        $checking = Order::beingChecked()
            ->with(['client:id,name', 'items.medicine:id,name,pack,dosage', 'items.pharmacy:id,name,phone'])
            ->orderBy('check_deadline_at')
            ->get();

        return view('admin.dashboard', [
            'counters'      => $this->counters(),
            'alerts'        => $checking->filter(fn (Order $o) => $o->secondsLeftForCheck() <= self::ALERT_THRESHOLD_SECONDS)->values(),
            'focus'         => $checking->first(),
            'pharmacies'    => Pharmacy::active()->orderBy('name')->get(),
            'queue'         => $this->queue(),
            'queueTabs'     => $this->queueTabs(),
            'assignable'    => Order::awaitingCourier()->with('client:id,name')->oldest()->first(),
            'couriers'      => $this->couriers(),
            'kpis'          => $this->kpis(),
            'onTheRoad'     => $this->onTheRoad(),
            'feed'          => OrderEvent::with('order:id')->latest()->limit(8)->get(),
            'partners'      => Pharmacy::active()->orderByDesc('reliability')->limit(5)->get(),
        ]);
    }

    /** Les cinq compteurs de tête. */
    private function counters(): array
    {
        $today = Carbon::today();

        return [
            [
                'label' => 'À valider',
                'value' => Order::awaitingManager()->count(),
                'delta' => Order::awaitingManager()->where('created_at', '>=', now()->subMinutes(10))->count() . ' / 10 min',
                'color' => '#B87514',
                'href'  => route('manager.verifications.index'),
            ],
            [
                'label' => 'En vérification',
                'value' => Order::beingChecked()->count(),
                'delta' => Order::beingChecked()->where('check_deadline_at', '<=', now()->addMinute())->count() . ' sous 1 min',
                'color' => '#A6382F',
                'href'  => route('manager.verifications.index'),
            ],
            [
                'label' => 'En livraison',
                'value' => Order::inDelivery()->count(),
                'delta' => 'ETA moy. ' . (int) round(Order::inDelivery()->avg('eta_minutes') ?? 0) . ' min',
                'color' => '#33557F',
                'href'  => route('manager.deliveries.index'),
            ],
            [
                'label' => "Livrées aujourd'hui",
                'value' => Order::withStatus([OrderStatus::DELIVERED, OrderStatus::RATED])->whereDate('delivered_at', $today)->count(),
                'delta' => $this->deliveredTrend(),
                'color' => '#0E5C43',
                'href'  => route('manager.orders.index', ['status' => OrderStatus::DELIVERED->value]),
            ],
            [
                'label' => 'Annulées',
                'value' => Order::withStatus([OrderStatus::CANCELED, OrderStatus::REFUSED])->whereDate('updated_at', $today)->count(),
                'delta' => Order::withStatus([OrderStatus::UNAVAILABLE])->whereDate('updated_at', $today)->count() . ' indispo.',
                'color' => '#5B6B63',
                'href'  => route('manager.orders.index', ['status' => OrderStatus::CANCELED->value]),
            ],
        ];
    }

    private function deliveredTrend(): string
    {
        $today     = Order::withStatus([OrderStatus::DELIVERED, OrderStatus::RATED])->whereDate('delivered_at', Carbon::today())->count();
        $yesterday = Order::withStatus([OrderStatus::DELIVERED, OrderStatus::RATED])->whereDate('delivered_at', Carbon::yesterday())->count();

        if ($yesterday === 0) {
            return $today > 0 ? 'premier jour' : 'vs hier';
        }

        $delta = (int) round(($today - $yesterday) / $yesterday * 100);

        return ($delta >= 0 ? '+' : '') . $delta . ' % vs hier';
    }

    /** Les six dernières commandes, tous statuts confondus. */
    private function queue(): Collection
    {
        return Order::with(['client:id,name', 'items'])
            ->latest()
            ->limit(6)
            ->get();
    }

    /** @return array<int, array{label: string, count: int, url: string, active: bool}> */
    private function queueTabs(): array
    {
        $current = request()->string('tab')->toString() ?: 'valider';

        $tabs = [
            'valider'      => ['À valider', Order::awaitingManager()->count(), route('manager.verifications.index')],
            'verification' => ['En vérification', Order::beingChecked()->count(), route('manager.verifications.index')],
            'livraison'    => ['En livraison', Order::inDelivery()->count(), route('manager.deliveries.index')],
            'livrees'      => ['Livrées', Order::withStatus([OrderStatus::DELIVERED, OrderStatus::RATED])->count(), route('manager.orders.index')],
        ];

        return collect($tabs)->map(fn (array $tab, string $key) => [
            'label'  => $tab[0],
            'count'  => $tab[1],
            'url'    => $tab[2],
            'active' => $key === $current,
        ])->values()->all();
    }

    /**
     * Livreurs classés pour l'attribution : disponibles d'abord, puis les plus
     * proches de la pharmacie de départ.
     */
    private function couriers(): Collection
    {
        $order    = Order::awaitingCourier()->with('pharmacy')->oldest()->first();
        $pharmacy = $order?->pharmacy ?? Pharmacy::active()->first();

        return User::couriers()
            ->withAvg('reviews as reviews_avg_rating', 'rating')
            ->withCount('reviews')
            ->orderByDesc('is_available')
            ->get()
            ->map(function (User $courier) use ($pharmacy) {
                $courier->setAttribute('distance_km', $courier->distanceToKm($pharmacy?->latitude, $pharmacy?->longitude));
                $courier->setAttribute('state', $courier->courierState());
                $courier->setAttribute('load', $courier->activeDeliveriesCount());

                return $courier;
            })
            ->sortBy([
                fn (User $a, User $b) => ($b->state === 'Disponible') <=> ($a->state === 'Disponible'),
                fn (User $a, User $b) => ($a->distance_km ?? 99) <=> ($b->distance_km ?? 99),
            ])
            ->take(5)
            ->values();
    }

    /** Les quatre indicateurs du panneau sombre. */
    private function kpis(): array
    {
        $settled = Order::withStatus([
            OrderStatus::AVAILABLE, OrderStatus::PARTIALLY_AVAILABLE, OrderStatus::UNAVAILABLE,
            OrderStatus::COURIER_ASSIGNED, OrderStatus::TO_PHARMACY, OrderStatus::AT_PHARMACY,
            OrderStatus::PICKED_UP, OrderStatus::TO_CLIENT, OrderStatus::DELIVERED, OrderStatus::RATED,
        ])->count();

        $obtained = Order::withStatus([
            OrderStatus::AVAILABLE, OrderStatus::COURIER_ASSIGNED, OrderStatus::TO_PHARMACY,
            OrderStatus::AT_PHARMACY, OrderStatus::PICKED_UP, OrderStatus::TO_CLIENT,
            OrderStatus::DELIVERED, OrderStatus::RATED,
        ])->count();

        $availability = $settled > 0 ? (int) round($obtained / $settled * 100) : 0;
        $checkSeconds = $this->averageSeconds('checking_started_at', 'verdict_at');
        $deliveryMin  = (int) round($this->averageSeconds('assigned_at', 'delivered_at') / 60);
        $rating       = round((float) CourierReview::avg('rating'), 1);

        return [
            [
                'label' => 'Taux de disponibilité',
                'value' => $availability . ' %',
                'bar'   => $availability . '%',
                'color' => '#6FBF9B',
            ],
            [
                'label' => 'Délai moyen de vérification',
                'value' => intdiv($checkSeconds, 60) . ':' . str_pad((string) ($checkSeconds % 60), 2, '0', STR_PAD_LEFT),
                // Rapporté aux 5 minutes annoncées au client.
                'bar'   => min(100, (int) round($checkSeconds / Order::CHECK_DURATION_SECONDS * 100)) . '%',
                'color' => '#F0B44A',
            ],
            [
                'label' => 'Délai moyen de livraison',
                'value' => $deliveryMin . ' min',
                'bar'   => min(100, (int) round($deliveryMin / 45 * 100)) . '%',
                'color' => '#8FB0D6',
            ],
            [
                'label' => 'Note moyenne des livreurs',
                'value' => $rating > 0 ? number_format($rating, 1, ',', ' ') : '—',
                'bar'   => (int) round($rating / 5 * 100) . '%',
                'color' => '#F0B44A',
            ],
        ];
    }

    /** Durée moyenne, en secondes, entre deux jalons d'une commande. */
    private function averageSeconds(string $from, string $to): int
    {
        $rows = Order::whereNotNull($from)->whereNotNull($to)
            ->latest()->limit(100)
            ->get([$from, $to]);

        if ($rows->isEmpty()) {
            return 0;
        }

        return (int) round($rows->avg(fn (Order $o) => $o->{$from}->diffInSeconds($o->{$to})));
    }

    /** Livreurs actuellement sur la route, pour la carte. */
    private function onTheRoad(): Collection
    {
        return Order::inDelivery()
            ->with(['assignment.courier:id,name,zone', 'pharmacy:id,name'])
            ->get()
            ->map(fn (Order $order) => [
                'courier' => $order->assignment?->courier?->short_name ?? 'Livreur',
                'status'  => $order->status,
                'label'   => match ($order->status) {
                    OrderStatus::TO_PHARMACY  => 'en route pharmacie',
                    OrderStatus::AT_PHARMACY  => 'attend en pharmacie',
                    OrderStatus::PICKED_UP    => 'médicaments récupérés',
                    OrderStatus::TO_CLIENT    => 'en route client',
                    default                   => 'course attribuée',
                },
                'eta'     => $order->eta_minutes ? $order->eta_minutes . ' min' : '—',
            ])
            ->take(6);
    }
}
