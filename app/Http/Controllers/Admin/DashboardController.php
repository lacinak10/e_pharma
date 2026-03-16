<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AssignmentStatus;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\DeliveryAssignment;
use App\Models\Medicine;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user      = Auth::user();
        $isCourier = $user->role === 'courier';

        $days        = collect(range(6, 0))->map(fn($i) => Carbon::today()->subDays($i));
        $labels7Days = $days->map(fn($d) => $d->locale('fr')->isoFormat('ddd'))->toArray();

        if (!$isCourier) {
            return $this->managerDashboard($days, $labels7Days);
        }

        return $this->courierDashboard($user, $days, $labels7Days);
    }

    private function managerDashboard($days, array $labels7Days)
    {
        $monthStart = Carbon::now()->startOfMonth();

        $ordersToday     = Order::whereDate('created_at', Carbon::today())->count();
        $revenueMonth    = (int) Order::where('status', OrderStatus::DELIVERED)
            ->whereBetween('created_at', [$monthStart, Carbon::now()])
            ->sum('total_amount');
        $productsInStock = Medicine::where('is_active', true)->where('stock', '>', 0)->count();
        $ruptures        = Medicine::where('is_active', true)->where('stock', '<=', 0)->count();

        $statsManager = [
            ['label' => "Commandes aujourd'hui", 'value' => $ordersToday, 'icon' => 'fa-solid fa-cart-shopping', 'valueClass' => 'text-primary', 'iconWrapClass' => 'bg-blue-100 text-primary'],
            ['label' => "CA du mois", 'value' => number_format($revenueMonth, 0, ',', ' ') . ' FCFA', 'icon' => 'fa-solid fa-coins', 'valueClass' => 'text-secondary', 'iconWrapClass' => 'bg-indigo-100 text-secondary'],
            ['label' => "Produits en stock", 'value' => $productsInStock, 'icon' => 'fa-solid fa-pills', 'valueClass' => 'text-accent', 'iconWrapClass' => 'bg-green-100 text-accent'],
            ['label' => "Ruptures", 'value' => $ruptures, 'icon' => 'fa-solid fa-triangle-exclamation', 'valueClass' => 'text-danger', 'iconWrapClass' => 'bg-red-100 text-danger'],
        ];

        $orders7Days  = $days->map(fn($d) => Order::whereDate('created_at', $d)->count())->toArray();
        $revenue7Days = $days->map(fn($d) => (int) Order::where('status', OrderStatus::DELIVERED)->whereDate('created_at', $d)->sum('total_amount'))->toArray();

        $statusBreakdownManager = [
            'labels' => ['En attente', 'Affectée', 'En livraison', 'Livrée', 'Annulée'],
            'values' => [
                Order::where('status', OrderStatus::PENDING_ASSIGNMENT)->count(),
                Order::where('status', OrderStatus::ASSIGNED)->count(),
                Order::where('status', OrderStatus::IN_DELIVERY)->count(),
                Order::where('status', OrderStatus::DELIVERED)->count(),
                Order::where('status', OrderStatus::CANCELED)->count(),
            ],
        ];

        $top = OrderItem::query()
            ->select('medicine_id', DB::raw('SUM(quantity) as qty'))
            ->whereHas('order', fn($q) => $q->where('status', OrderStatus::DELIVERED))
            ->groupBy('medicine_id')
            ->orderByDesc('qty')
            ->with('medicine:id,name')
            ->limit(5)
            ->get();

        $topMedicines = [
            'labels' => $top->map(fn($x) => optional($x->medicine)->name ?? 'N/A')->toArray(),
            'values' => $top->map(fn($x) => (int) $x->qty)->toArray(),
        ];

        $recentOrders = Order::with('user:id,name')
            ->latest()
            ->limit(6)
            ->get()
            ->map(fn($o) => [
                'id'       => '#EP-' . $o->id,
                'customer' => $o->user->name ?? 'Client',
                'date'     => $o->created_at->format('d/m/Y'),
                'amount'   => number_format((int) $o->total_amount, 0, ',', ' ') . ' FCFA',
                'status'   => $o->status->label(),
                'badge'    => $this->orderStatusBadge($o->status),
            ])->toArray();

        return view('admin.dashboard', compact(
            'labels7Days', 'statsManager', 'orders7Days', 'revenue7Days',
            'statusBreakdownManager', 'topMedicines', 'recentOrders'
        ));
    }

    private function courierDashboard($user, $days, array $labels7Days)
    {
        $weekStart = Carbon::now()->startOfWeek();

        $assigned          = DeliveryAssignment::where('courier_id', $user->id)->where('status', AssignmentStatus::ASSIGNED)->count();
        $inProgress        = DeliveryAssignment::where('courier_id', $user->id)->whereIn('status', [AssignmentStatus::ACCEPTED, AssignmentStatus::DELIVERING])->count();
        $deliveredThisWeek = DeliveryAssignment::where('courier_id', $user->id)
            ->where('status', AssignmentStatus::DELIVERED)
            ->whereBetween('updated_at', [$weekStart, Carbon::now()])
            ->count();

        $acceptRate = $this->computeAcceptRate($user->id);

        $statsCourier = [
            ['label' => "Assignées", 'value' => $assigned, 'icon' => 'fa-solid fa-inbox', 'valueClass' => 'text-primary', 'iconWrapClass' => 'bg-blue-100 text-primary'],
            ['label' => "En cours", 'value' => $inProgress, 'icon' => 'fa-solid fa-truck', 'valueClass' => 'text-secondary', 'iconWrapClass' => 'bg-indigo-100 text-secondary'],
            ['label' => "Livrées (semaine)", 'value' => $deliveredThisWeek, 'icon' => 'fa-solid fa-circle-check', 'valueClass' => 'text-accent', 'iconWrapClass' => 'bg-green-100 text-accent'],
            ['label' => "Taux d'acceptation", 'value' => $acceptRate . '%', 'icon' => 'fa-solid fa-thumbs-up', 'valueClass' => 'text-gray-800', 'iconWrapClass' => 'bg-gray-100 text-gray-700'],
        ];

        $deliveries7Days = $days->map(fn($d) => DeliveryAssignment::where('courier_id', $user->id)
            ->where('status', AssignmentStatus::DELIVERED)
            ->whereDate('updated_at', $d)
            ->count()
        )->toArray();

        $statusBreakdownCourier = [
            'labels' => ['Assignée', 'Acceptée', 'En livraison', 'Livrée', 'Refusée'],
            'values' => [
                DeliveryAssignment::where('courier_id', $user->id)->where('status', AssignmentStatus::ASSIGNED)->count(),
                DeliveryAssignment::where('courier_id', $user->id)->where('status', AssignmentStatus::ACCEPTED)->count(),
                DeliveryAssignment::where('courier_id', $user->id)->where('status', AssignmentStatus::DELIVERING)->count(),
                DeliveryAssignment::where('courier_id', $user->id)->where('status', AssignmentStatus::DELIVERED)->count(),
                DeliveryAssignment::where('courier_id', $user->id)->where('status', AssignmentStatus::REFUSED)->count(),
            ],
        ];

        $recentOrders = DeliveryAssignment::with('order.user:id,name')
            ->where('courier_id', $user->id)
            ->latest()
            ->limit(6)
            ->get()
            ->map(function ($a) {
                $o = $a->order;
                return [
                    'id'       => '#EP-' . ($o->id ?? $a->order_id),
                    'customer' => ($o->user->name ?? 'Client') . ' — ' . str($o->delivery_address ?? '')->limit(24),
                    'date'     => optional($o?->created_at)->format('d/m/Y') ?? now()->format('d/m/Y'),
                    'amount'   => number_format((int) ($o->total_amount ?? 0), 0, ',', ' ') . ' FCFA',
                    'status'   => $a->status->label(),
                    'badge'    => $a->status->badge(),
                ];
            })->toArray();

        $deliveryGoal = 20;
        $progressPct  = min(100, (int) round(($deliveredThisWeek / max(1, $deliveryGoal)) * 100));

        return view('admin.dashboard', compact(
            'labels7Days', 'statsCourier', 'deliveries7Days', 'statusBreakdownCourier',
            'recentOrders', 'deliveryGoal', 'deliveredThisWeek', 'progressPct'
        ));
    }

    private function orderStatusBadge(OrderStatus $s): string
    {
        return match ($s) {
            OrderStatus::PENDING_ASSIGNMENT => 'yellow',
            OrderStatus::ASSIGNED           => 'blue',
            OrderStatus::ACCEPTED           => 'cyan',
            OrderStatus::IN_DELIVERY        => 'indigo',
            OrderStatus::DELIVERED          => 'green',
            OrderStatus::CANCELED           => 'red',
            OrderStatus::REFUSED            => 'orange',
        };
    }

    private function computeAcceptRate(int $courierId): int
    {
        $total = DeliveryAssignment::where('courier_id', $courierId)
            ->whereIn('status', [
                AssignmentStatus::ASSIGNED,
                AssignmentStatus::ACCEPTED,
                AssignmentStatus::REFUSED,
                AssignmentStatus::DELIVERING,
                AssignmentStatus::DELIVERED,
            ])->count();

        if ($total <= 0) {
            return 0;
        }

        $accepted = DeliveryAssignment::where('courier_id', $courierId)
            ->whereIn('status', [
                AssignmentStatus::ACCEPTED,
                AssignmentStatus::DELIVERING,
                AssignmentStatus::DELIVERED,
            ])->count();

        return (int) round(($accepted / $total) * 100);
    }
}
