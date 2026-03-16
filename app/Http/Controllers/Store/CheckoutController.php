<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(private OrderService $orderService)
    {
    }

    public function create()
    {
        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()->route('store.cart.index')->with('error', 'Votre panier est vide.');
        }

        return view('store.checkout.index', compact('cart'));
    }

    public function store(Request $request)
    {
        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()->route('store.cart.index')->with('error', 'Votre panier est vide.');
        }

        $data = $request->validate([
            'delivery_address' => ['required', 'string', 'min:5', 'max:255'],
            'delivery_phone'   => ['required', 'string', 'min:8', 'max:30'],
            'notes'            => ['nullable', 'string', 'max:500'],
            'payment_method'   => ['required', 'in:cash,momo,card'],
        ]);

        $order = $this->orderService->createFromCart(auth()->id(), $cart, $data);

        session()->forget('cart');

        return redirect()
            ->route('store.orders.show', $order)
            ->with('success', 'Commande créée avec succès !');
    }
}
