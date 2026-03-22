@extends('layouts.store')

@section('title', 'Checkout — E-PHARMA')

@section('content')
@php
    $subtotal = collect($cart)->sum(fn($i) => (int)$i['price'] * (int)$i['qty']);
    $delivery = $subtotal > 0 ? 1500 : 0;
    $total = $subtotal + $delivery;
@endphp

<section class="max-w-7xl mx-auto px-4 py-10">
    <h1 class="text-3xl font-extrabold">Finaliser la commande</h1>
    <p class="text-gray-600 mt-1">Adresse + paiement + récapitulatif.</p>

    <form class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-6" method="POST" action="{{ route('store.checkout.store') }}">
        @csrf

        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h3 class="text-lg font-extrabold">Livraison</h3>
                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-store.input name="delivery_phone" label="Téléphone" placeholder="Ex : 0505050505" value="{{ old('delivery_phone') }}" />
                    <x-store.input name="delivery_address" label="Adresse" placeholder="Ex: Cocody Angré, Rue..." value="{{ old('delivery_address') }}" />
                </div>
                <div class="mt-4">
                    <label class="block">
                        <span class="block text-sm font-semibold text-gray-700 mb-1">Note (optionnel)</span>
                        <textarea name="notes" rows="3" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-blue-600 focus:border-transparent">{{ old('notes') }}</textarea>
                        @error('notes') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    </label>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h3 class="text-lg font-extrabold">Paiement</h3>
                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3">
                    <label class="p-4 rounded-2xl border border-gray-200 hover:border-blue-300 cursor-pointer">
    <input type="radio" name="payment_method" value="cash" class="mr-2" checked>
    Espèces
</label>

<label class="p-4 rounded-2xl border border-gray-200 hover:border-blue-300 cursor-pointer opacity-50 cursor-not-allowed">
    <input type="radio" name="payment_method" value="momo" class="mr-2" disabled>
    Mobile Money
</label>

<label class="p-4 rounded-2xl border border-gray-200 hover:border-blue-300 cursor-pointer opacity-50 cursor-not-allowed">
    <input type="radio" name="payment_method" value="card" class="mr-2" disabled>
    Carte
</label>
                </div>

                @error('payment_method') <span class="text-sm text-red-600 mt-2 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h3 class="text-lg font-extrabold">Récapitulatif</h3>

                <div class="mt-4 space-y-3 text-sm">
                    @foreach($cart as $i)
                        <div class="flex justify-between text-gray-700">
                            <span class="line-clamp-1">{{ $i['name'] }} × {{ (int)$i['qty'] }}</span>
                            <span class="font-semibold">{{ number_format(((int)$i['price']*(int)$i['qty']), 0, ',', ' ') }} FCFA</span>
                        </div>
                    @endforeach

                    <div class="border-t border-gray-100 pt-3 space-y-2">
                        <div class="flex justify-between text-gray-600">
                            <span>Sous-total</span>
                            <span class="font-semibold text-gray-900">{{ number_format($subtotal, 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Livraison</span>
                            <span class="font-semibold text-gray-900">{{ number_format($delivery, 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-bold text-gray-900">Total</span>
                            <span class="font-extrabold text-blue-700">{{ number_format($total, 0, ',', ' ') }} FCFA</span>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="total_amount" value="{{ $total }}" class="mr-2">
                <input type="hidden" name="subtotal" value="{{ $subtotal }}" class="mr-2">
                <input type="hidden" name="delivery_fee" value="{{ $delivery }}" class="mr-2">




                <div class="mt-6">
                    <x-store.button type="submit" variant="primary" class="w-full">
                        <i class="fa-solid fa-check"></i> Confirmer la commande
                    </x-store.button>
                    <p class="text-xs text-gray-500 mt-2">
                        En cliquant, vous confirmez votre commande.
                    </p>
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-100 rounded-2xl p-6 text-sm text-blue-900">
                <b>Conseil</b> : pour une livraison rapide, ajoute un repère (immeuble, portail, etc.).
            </div>
        </div>
    </form>
</section>
@endsection
