<?php

namespace App\Http\Controllers\Client;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Models\Medicine;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::query()
            ->where('user_id', auth()->id())
            ->with(['items.medicine', 'assignment.courier'])
            ->latest()
            ->paginate(15);

        return view('client.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);

        $order->load(['items.medicine', 'assignment.courier']);

        return view('client.orders.show', compact('order'));
    }

    /**
     * Checkout => crée la commande à partir du panier session
     */
    public function store(CheckoutRequest $request)
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Votre panier est vide.');
        }

        $payload = $request->validated();

        $order = DB::transaction(function () use ($cart, $payload) {
            $order = Order::create([
                'user_id' => auth()->id(),
                'status' => OrderStatus::PENDING_ASSIGNMENT,
                'delivery_address' => $payload['delivery_address'],
                'delivery_phone' => $payload['delivery_phone'] ?? null,
                'notes' => $payload['notes'] ?? null,
                'total_amount' => 0,
            ]);

            $total = 0;

            foreach ($cart as $medicineId => $qty) {
                $qty = (int) $qty;

                $medicine = Medicine::query()
                    ->whereKey((int) $medicineId)
                    ->lockForUpdate()
                    ->first();

                if (!$medicine || !$medicine->is_active) {
                    throw ValidationException::withMessages([
                        'cart' => "Un article du panier n'est plus disponible (ID: {$medicineId}).",
                    ]);
                }

                if ($qty > $medicine->stock) {
                    throw ValidationException::withMessages([
                        'cart' => "Stock insuffisant pour {$medicine->name}. Stock dispo: {$medicine->stock}.",
                    ]);
                }

                // décrément stock
                $medicine->decrement('stock', $qty);

                $unitPrice = (int) $medicine->price;
                $lineTotal = $unitPrice * $qty;
                $total += $lineTotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'medicine_id' => $medicine->id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ]);
            }

            $order->update(['total_amount' => $total]);

            return $order;
        });

        session()->forget('cart');

        return redirect()
            ->route('client.orders.show', $order)
            ->with('success', 'Commande créée. En attente d’un livreur.');
    }

    /**
     * Annule la commande (status CANCELED) - resource destroy
     */
    public function destroy(Order $order)
    {
        $this->authorize('cancel', $order);

        if ($order->status === OrderStatus::CANCELED) {
            return back()->with('error', 'Commande déjà annulée.');
        }

        $order->update([
            'status' => OrderStatus::CANCELED,
            'canceled_at' => now(),
        ]);

        return redirect()
            ->route('client.orders.index')
            ->with('success', 'Commande annulée.');
    }
}
