<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryAssignment;
use App\Models\Medicine;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Enums\OrderStatus;



class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $role = $user->role ?? null;
        $isCourier = $role === 'courier';

        // Labels 7 jours (Lun..Dim) + séries
        $days = collect(range(6,0))->map(fn($i) => Carbon::today()->subDays($i));
        $labels7Days = $days->map(fn($d) => $d->locale('fr')->isoFormat('ddd'))->toArray();

        if (!$isCourier) {
            // ===== MANAGER =====

            $ordersToday = Order::whereDate('created_at', Carbon::today())->count();

            $monthStart = Carbon::now()->startOfMonth();
            $revenueMonth = (int) Order::where('status', 'delivered')
                ->whereBetween('created_at', [$monthStart, Carbon::now()])
                ->sum('total_amount');

            $productsInStock = Medicine::where('is_active', true)->where('stock', '>', 0)->count();
            $ruptures = Medicine::where('is_active', true)->where('stock', '<=', 0)->count();

            $statsManager = [
                ['label'=>"Commandes aujourd'hui", 'value'=>$ordersToday, 'icon'=>'fa-solid fa-cart-shopping', 'valueClass'=>'text-primary', 'iconWrapClass'=>'bg-blue-100 text-primary'],
                ['label'=>"CA du mois", 'value'=>number_format($revenueMonth, 0, ',', ' ').' FCFA', 'icon'=>'fa-solid fa-coins', 'valueClass'=>'text-secondary', 'iconWrapClass'=>'bg-indigo-100 text-secondary'],
                ['label'=>"Produits en stock", 'value'=>$productsInStock, 'icon'=>'fa-solid fa-pills', 'valueClass'=>'text-accent', 'iconWrapClass'=>'bg-green-100 text-accent'],
                ['label'=>"Ruptures", 'value'=>$ruptures, 'icon'=>'fa-solid fa-triangle-exclamation', 'valueClass'=>'text-danger', 'iconWrapClass'=>'bg-red-100 text-danger'],
            ];

            $orders7Days = $days->map(function($d){
                return Order::whereDate('created_at', $d)->count();
            })->toArray();

            $revenueMonth = (int) Order::where('status', OrderStatus::DELIVERED)
            ->whereBetween('created_at', [$monthStart, Carbon::now()])
            ->sum('total_amount');

            $revenue7Days = $days->map(function($d){
                return (int) Order::where('status', OrderStatus::DELIVERED)
                    ->whereDate('created_at', $d)
                    ->sum('total_amount');
            })->toArray();

            $statusBreakdownManager = [
                'labels' => ['En attente', 'Affectée', 'En livraison', 'Livrée', 'Annulée'],
                'values' => [
                    Order::where('status', OrderStatus::PENDING_ASSIGNMENT)->count(),
                    Order::where('status', OrderStatus::ASSIGNED)->count(),
                    Order::where('status', OrderStatus::IN_DELIVERY)->count(),
                    Order::where('status', OrderStatus::DELIVERED)->count(),
                    Order::where('status', OrderStatus::CANCELED)->count(),
                ]
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
                'values' => $top->map(fn($x) => (int)$x->qty)->toArray(),
            ];

            $recentOrders = Order::with('user:id,name')
                ->latest()
                ->limit(6)
                ->get()
                ->map(function($o){
                    return [
                        'id' => '#EP-'.$o->id,
                        'customer' => $o->user->name ?? 'Client',
                        'date' => $o->created_at->format('d/m/Y'),
                        'amount' => number_format((int)$o->total_amount, 0, ',', ' ') . ' FCFA',
                        'status' => $this->statusLabel($o->status),
                        'badge' => $this->statusBadge($o->status),
                    ];
                })->toArray();

            return view('admin.dashboard', compact(
                'labels7Days','statsManager','orders7Days','revenue7Days',
                'statusBreakdownManager','topMedicines','recentOrders'
            ));
        }

        // ===== COURIER =====
        $assigned = DeliveryAssignment::where('courier_id', $user->id)->where('status','assigned')->count();
        $inProgress = DeliveryAssignment::where('courier_id', $user->id)->whereIn('status',['accepted','delivering'])->count();

        $weekStart = Carbon::now()->startOfWeek();
        $deliveredThisWeek = DeliveryAssignment::where('courier_id', $user->id)
            ->where('status','delivered')
            ->whereBetween('updated_at', [$weekStart, Carbon::now()])
            ->count();

        $acceptRate = $this->acceptRate($user->id);

        $statsCourier = [
            ['label'=>"Assignées", 'value'=>$assigned, 'icon'=>'fa-solid fa-inbox', 'valueClass'=>'text-primary', 'iconWrapClass'=>'bg-blue-100 text-primary'],
            ['label'=>"En cours", 'value'=>$inProgress, 'icon'=>'fa-solid fa-truck', 'valueClass'=>'text-secondary', 'iconWrapClass'=>'bg-indigo-100 text-secondary'],
            ['label'=>"Livrées (semaine)", 'value'=>$deliveredThisWeek, 'icon'=>'fa-solid fa-circle-check', 'valueClass'=>'text-accent', 'iconWrapClass'=>'bg-green-100 text-accent'],
            ['label'=>"Taux d’acceptation", 'value'=>$acceptRate.'%', 'icon'=>'fa-solid fa-thumbs-up', 'valueClass'=>'text-gray-800', 'iconWrapClass'=>'bg-gray-100 text-gray-700'],
        ];

        $deliveries7Days = $days->map(function($d) use ($user){
            return DeliveryAssignment::where('courier_id',$user->id)
                ->where('status','delivered')
                ->whereDate('updated_at',$d)
                ->count();
        })->toArray();

        $statusBreakdownCourier = [
            'labels' => ['Assignée','Acceptée','En livraison','Livrée','Refusée'],
            'values' => [
                DeliveryAssignment::where('courier_id',$user->id)->where('status','assigned')->count(),
                DeliveryAssignment::where('courier_id',$user->id)->where('status','accepted')->count(),
                DeliveryAssignment::where('courier_id',$user->id)->where('status','delivering')->count(),
                DeliveryAssignment::where('courier_id',$user->id)->where('status','delivered')->count(),
                DeliveryAssignment::where('courier_id',$user->id)->where('status','refused')->count(),
            ]
        ];

        $recentOrders = DeliveryAssignment::with('order.user:id,name')
            ->where('courier_id',$user->id)
            ->latest()
            ->limit(6)
            ->get()
            ->map(function($a){
                $o = $a->order;
                return [
                    'id' => '#EP-'.($o->id ?? $a->order_id),
                    'customer' => ($o->user->name ?? 'Client') . ' — ' . str($o->delivery_address ?? '')->limit(24),
                    'date' => optional($o?->created_at)->format('d/m/Y') ?? now()->format('d/m/Y'),
                    'amount' => number_format((int)($o->total_amount ?? 0), 0, ',', ' ') . ' FCFA',
                    'status' => $this->assignLabel($a->status),
                    'badge' => $this->assignBadge($a->status),
                ];
            })->toArray();

        $deliveryGoal = 20;
        $progressPct = min(100, (int) round(($deliveredThisWeek / max(1,$deliveryGoal)) * 100));

        return view('admin.dashboard', compact(
            'labels7Days','statsCourier','deliveries7Days','statusBreakdownCourier',
            'recentOrders','deliveryGoal','deliveredThisWeek','progressPct'
        ));
    }

    private function statusLabel(OrderStatus $s): string
{
    return match ($s) {
        OrderStatus::PENDING_ASSIGNMENT => 'En attente',
        OrderStatus::ASSIGNED           => 'Affectée',
        OrderStatus::IN_DELIVERY        => 'En cours de livraison',
        OrderStatus::DELIVERED          => 'Livrée',
        OrderStatus::CANCELED           => 'Annulée',
        OrderStatus::ACCEPTED           => 'Acceptée par livreur',
        OrderStatus::REFUSED            => 'Refusée',
        default                         => $s->name, // ou $s->value si ton enum est "enum: string"
    };
}


private function statusBadge(OrderStatus $s): string
{
    return match ($s) {
        OrderStatus::PENDING_ASSIGNMENT => 'yellow',
        OrderStatus::ASSIGNED => 'blue',
        OrderStatus::IN_DELIVERY => 'indigo',
        OrderStatus::DELIVERED => 'green',
        OrderStatus::CANCELED => 'red',
        OrderStatus::ACCEPTED => 'cyan',
        OrderStatus::REFUSED => 'orange',
        default => 'gray',
    };
}
    private function assignLabel(string $s): string
    {
        return match($s){
            'assigned' => 'Assignée',
            'accepted' => 'Acceptée',
            'delivering' => 'En livraison',
            'delivered' => 'Livrée',
            'refused' => 'Refusée',
            default => $s,
        };
    }
    private function assignBadge(string $s): string
    {
        return match($s){
            'assigned' => 'blue',
            'accepted' => 'yellow',
            'delivering' => 'indigo',
            'delivered' => 'green',
            'refused' => 'red',
            default => 'gray',
        };
    }

    private function acceptRate(int $courierId): int
    {
        $total = DeliveryAssignment::where('courier_id',$courierId)->whereIn('status',['assigned','accepted','refused','delivering','delivered'])->count();
        if ($total <= 0) return 0;

        $accepted = DeliveryAssignment::where('courier_id',$courierId)->whereIn('status',['accepted','delivering','delivered'])->count();
        return (int) round(($accepted / $total) * 100);
    }
}
