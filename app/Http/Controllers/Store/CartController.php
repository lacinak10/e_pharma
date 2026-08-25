<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart = session('cart', []);
        return view('store.cart.index', [
            'cart' => $cart,
        ]);
    }

    public function store(Request $request, Medicine $medicine)
    {
        abort_unless($medicine->is_active, 404);

        $request->validate([
            'qty' => ['nullable','integer','min:1','max:99'],
        ]);

        $qty = (int) ($request->input('qty', 1));
        $cart = session('cart', []);

        $currentQty = (int) ($cart[$medicine->id]['qty'] ?? 0);
        $newQty = $currentQty + $qty;

        // Aucun contrôle de stock : la disponibilité réelle est établie après
        // la commande, par téléphone, auprès des pharmacies partenaires.
        $cart[$medicine->id] = [
            'id' => $medicine->id,
            'name' => $medicine->name,
            'price' => (int) $medicine->price,
            'image_url' => $medicine->image_url,
            'requires_prescription' => (bool) $medicine->requires_prescription,
            'qty' => $newQty,
        ];

        session(['cart' => $cart]);

        return back()->with('success', "{$medicine->name} ajouté au panier.");
    }

    public function update(Request $request, Medicine $medicine)
    {
        $request->validate([
            'qty' => ['required','integer','min:1','max:99'],
        ]);

        $cart = session('cart', []);
        if (!isset($cart[$medicine->id])) {
            return back()->with('error', "Produit introuvable dans le panier.");
        }

        $qty = (int) $request->qty;

        $cart[$medicine->id]['qty'] = $qty;

        session(['cart' => $cart]);

        return back()->with('success', "Quantité mise à jour.");
    }

    public function destroy(Medicine $medicine)
    {
        $cart = session('cart', []);
        unset($cart[$medicine->id]);
        session(['cart' => $cart]);

        return back()->with('success', "Produit supprimé du panier.");
    }

    public function clear()
    {
        session()->forget('cart');
        return back()->with('success', "Panier vidé.");
    }
}
