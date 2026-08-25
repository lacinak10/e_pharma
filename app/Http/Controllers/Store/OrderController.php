<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private OrderWorkflow $workflow)
    {
    }

    public function index(): View
    {
        $orders = Order::query()
            ->where('user_id', auth()->id())
            ->with(['items', 'assignment.courier:id,name,phone', 'review'])
            ->latest()
            ->paginate(10);

        return view('store.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $this->authorize('view', $order);

        $order->load([
            'items.medicine:id,name,pack,dosage,image_url',
            'pharmacy:id,name,area,phone',
            'assignment.courier',
            'events',
            'review',
        ]);

        return view('store.orders.show', compact('order'));
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('cancel', $order);

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $this->workflow->cancel($order, $request->user(), $validated['reason'] ?? null);

        return back()->with('success', 'Votre commande a été annulée.');
    }
}
