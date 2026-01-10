<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Order;

class OrderController extends Controller
{
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

        $order->load(['items.medicine']);

        return view('store.orders.show', compact('order'));
    }

    public function cancel(Order $order)
    {

        // Annulable seulement si pas encore en livraison
        if (!in_array($order->status, ['PENDING_ASSIGNMENT', 'ASSIGNED'], true)) {
            return back()->with('error', "Cette commande ne peut plus être annulée.");
        }

        $order->update(['status' => 'CANCELED']);

        return back()->with('success', "Commande annulée.");
    }
}
