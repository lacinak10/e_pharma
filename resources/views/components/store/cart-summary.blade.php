@props(['cart'])

@php
    $subtotal = collect($cart)->sum(fn($i) => (int)$i['price'] * (int)$i['qty']);
    $delivery = $subtotal > 0 ? 1500 : 0;
    $total = $subtotal + $delivery;
@endphp

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
    <h3 class="text-lg font-extrabold text-gray-900">Récapitulatif</h3>

    <div class="mt-4 space-y-2 text-sm">
        <div class="flex justify-between text-gray-600">
            <span>Sous-total</span>
            <span class="font-semibold text-gray-900">{{ number_format($subtotal, 0, ',', ' ') }} FCFA</span>
        </div>
        <div class="flex justify-between text-gray-600">
            <span>Livraison</span>
            <span class="font-semibold text-gray-900">{{ number_format($delivery, 0, ',', ' ') }} FCFA</span>
        </div>
        <div class="border-t border-gray-100 pt-3 flex justify-between">
            <span class="font-bold text-gray-900">Total</span>
            <span class="font-extrabold text-blue-700">{{ number_format($total, 0, ',', ' ') }} FCFA</span>
        </div>
    </div>

    {{ $slot }}
</div>
