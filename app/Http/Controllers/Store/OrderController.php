<?php

namespace App\Http\Controllers\Store;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService)
    {
    }

    public function index()
    {
        $orders = Order::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('store.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);

        $order->load(['items.medicine', 'assignment.courier']);

        return view('store.orders.show', compact('order'));
    }

    public function cancel(Order $order)
    {
        $this->authorize('cancel', $order);

        $cancelable = [
            OrderStatus::PENDING_ASSIGNMENT,
            OrderStatus::ASSIGNED,
            OrderStatus::REFUSED,
        ];

        if (!in_array($order->status, $cancelable, true)) {
            return back()->with('error', 'Cette commande ne peut plus être annulée.');
        }

        $this->orderService->cancel($order);

        return back()->with('success', 'Commande annulée.');
    }
}
