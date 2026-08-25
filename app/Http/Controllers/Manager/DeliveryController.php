<?php

namespace App\Http\Controllers\Manager;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Suivi des courses en cours — étapes 7 à 12.
 */
class DeliveryController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();

        $orders = Order::query()
            ->with(['client:id,name', 'pharmacy:id,name,area', 'assignment.courier:id,name,phone'])
            ->when(
                $status !== '' && OrderStatus::tryFrom($status),
                fn ($q) => $q->where('status', $status),
                fn ($q) => $q->inDelivery(),
            )
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $steps = [
            OrderStatus::COURIER_ASSIGNED,
            OrderStatus::TO_PHARMACY,
            OrderStatus::AT_PHARMACY,
            OrderStatus::PICKED_UP,
            OrderStatus::TO_CLIENT,
            OrderStatus::DELIVERED,
        ];

        return view('admin.deliveries.index', [
            'orders' => $orders,
            'status' => $status,
            'tabs'   => collect($steps)->map(fn (OrderStatus $s) => [
                'status' => $s,
                'count'  => Order::where('status', $s->value)->count(),
            ]),
        ]);
    }
}
