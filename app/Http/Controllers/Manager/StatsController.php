<?php

namespace App\Http\Controllers\Manager;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\CourierReview;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StatsController extends Controller
{
    public function index(): View
    {
        $days = collect(range(13, 0))->map(fn (int $i) => Carbon::today()->subDays($i));

        $top = OrderItem::query()
            ->select('medicine_name', DB::raw('SUM(quantity) as qty'))
            ->whereHas('order', fn ($q) => $q->where('status', OrderStatus::DELIVERED->value))
            ->groupBy('medicine_name')
            ->orderByDesc('qty')
            ->limit(8)
            ->get();

        return view('admin.stats.index', [
            'labels'    => $days->map(fn (Carbon $d) => $d->locale('fr')->isoFormat('DD/MM'))->all(),
            'orders'    => $days->map(fn (Carbon $d) => Order::whereDate('created_at', $d)->count())->all(),
            'delivered' => $days->map(fn (Carbon $d) => Order::withStatus([OrderStatus::DELIVERED, OrderStatus::RATED])
                ->whereDate('delivered_at', $d)->count())->all(),
            'revenue'   => $days->map(fn (Carbon $d) => (int) Order::withStatus([OrderStatus::DELIVERED, OrderStatus::RATED])
                ->whereDate('delivered_at', $d)->sum('total_amount'))->all(),
            'top'       => $top,
            'funnel'    => collect(OrderStatus::lifecycle())->mapWithKeys(fn (OrderStatus $s) => [
                $s->badge() => Order::where('status', $s->value)->count(),
            ])->filter(),
            'rating'    => round((float) CourierReview::avg('rating'), 1),
        ]);
    }
}
