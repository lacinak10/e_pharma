<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function create()
    {
        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('store.cart.index')->with('error', "Votre panier est vide.");
        }

        return view('store.checkout.index', [
            'cart' => $cart,
        ]);
    }

    public function store(Request $request)
    {
        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('store.cart.index')->with('error', "Votre panier est vide.");
        }

        $data = $request->validate([
            'delivery_address' => ['required','string','min:5','max:255'],
            'delivery_phone' => ['required','string','min:8','max:30'],
            'notes' => ['nullable','string','max:500'],
            'payment_method' => ['required','in:cash,momo,card'],
            'total_amount' => ['required'],
            'subtotal' => ['required', 'nullable'],
            'delivery_fee' =>  ['required', 'nullable']

        ]);

        return DB::transaction(function () use ($cart, $data) {
            // Re-check stock with lock
            $subtotal = 0;

            $medicines = Medicine::query()
                ->whereIn('id', array_keys($cart))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($cart as $line) {
                $m = $medicines->get($line['id']);
                if (!$m || !$m->is_active) {
                    abort(400, "Un article n'est plus disponible.");
                }

                if ($m->stock < $line['qty']) {
                    abort(400, "Stock insuffisant pour {$m->name}. Disponible: {$m->stock}.");
                }

                $subtotal += ((int) $m->price) * ((int) $line['qty']);
            }

            $deliveryFee = 1500; // simple; à rendre dynamique plus tard
            $total = $subtotal + $deliveryFee;

            $order = Order::create([
                'user_id' => auth()->id(),
                'status' => 'PENDING_ASSIGNMENT',
                'delivery_address' => $data['delivery_address'],
                'delivery_phone' => $data['delivery_phone'],
                'notes' => $data['notes'] ?? null,
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'total_amount' => $total,
                'payment_method' => $data['payment_method'],
            ]);

            foreach ($cart as $line) {
                $m = $medicines->get($line['id']);

                OrderItem::create([
                    'order_id' => $order->id,
                    'medicine_id' => $m->id,
                    'name' => $m->name,
                    'unit_price' => (int) $m->price,
                    'quantity' => (int) $line['qty'],
                    'line_total' => ((int) $m->price) * ((int) $line['qty']),
                ]);

                // decrement stock
                $m->decrement('stock', (int) $line['qty']);
            }

            session()->forget('cart');

            return redirect()
                ->route('store.orders.show', $order)
                ->with('success', "Commande créée avec succès !");
        });
    }
}
