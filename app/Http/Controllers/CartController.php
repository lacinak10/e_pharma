<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []); // [medicine_id => qty]

        $ids = array_keys($cart);
        $medicines = $ids
            ? Medicine::whereIn('id', $ids)->get()->keyBy('id')
            : collect();

        $items = [];
        $total = 0;

        foreach ($cart as $medicineId => $qty) {
            /** @var Medicine|null $medicine */
            $medicine = $medicines->get((int) $medicineId);
            if (!$medicine) {
                continue;
            }

            $lineTotal = $medicine->price * (int) $qty;
            $total += $lineTotal;

            $items[] = [
                'medicine' => $medicine,
                'quantity' => (int) $qty,
                'line_total' => $lineTotal,
            ];
        }

        return view('cart.index', compact('items', 'total'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'medicine_id' => ['required', 'integer', 'exists:medicines,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $medicine = Medicine::findOrFail($data['medicine_id']);
        abort_if(!$medicine->is_active, 404);

        $qty = (int) $data['quantity'];
        if ($qty > $medicine->stock) {
            return back()->withErrors([
                'quantity' => "Stock insuffisant pour {$medicine->name}. Stock dispo: {$medicine->stock}.",
            ])->withInput();
        }

        $cart = session()->get('cart', []);
        $current = (int) ($cart[$medicine->id] ?? 0);
        $newQty = $current + $qty;

        if ($newQty > $medicine->stock) {
            return back()->withErrors([
                'quantity' => "Stock insuffisant pour {$medicine->name}. Stock dispo: {$medicine->stock}.",
            ])->withInput();
        }

        $cart[$medicine->id] = $newQty;
        session()->put('cart', $cart);

        return redirect()->route('cart.index')->with('success', 'Ajouté au panier.');
    }

    public function update(Request $request, Medicine $medicine)
    {
        abort_if(!$medicine->is_active, 404);

        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $qty = (int) $data['quantity'];
        if ($qty > $medicine->stock) {
            return back()->withErrors([
                'quantity' => "Stock insuffisant pour {$medicine->name}. Stock dispo: {$medicine->stock}.",
            ])->withInput();
        }

        $cart = session()->get('cart', []);
        if (!array_key_exists($medicine->id, $cart)) {
            return redirect()->route('cart.index')->with('error', 'Article introuvable dans le panier.');
        }

        $cart[$medicine->id] = $qty;
        session()->put('cart', $cart);

        return redirect()->route('cart.index')->with('success', 'Quantité mise à jour.');
    }

    public function destroy(Medicine $medicine)
    {
        $cart = session()->get('cart', []);
        unset($cart[$medicine->id]);
        session()->put('cart', $cart);

        return redirect()->route('cart.index')->with('success', 'Article supprimé du panier.');
    }
}
