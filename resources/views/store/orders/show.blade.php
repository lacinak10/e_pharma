@extends('layouts.store')

@section('title', 'Commande #'.$order->id.' — E-PHARMA')

@section('content')
<section class="max-w-7xl mx-auto px-4 py-10">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
        <div>
            <a href="{{ route('store.orders.index') }}" class="text-sm text-blue-700 font-semibold hover:underline">← Mes commandes</a>
            <h1 class="mt-2 text-3xl font-extrabold">Commande #{{ $order->id }}</h1>
            <p class="text-gray-600 mt-1">{{ $order->created_at->format('d/m/Y H:i') }}</p>
        </div>

        <div class="flex items-center gap-3">
            <x-store.order-status :status="$order->status" />

            @if(in_array($order->status, ['PENDING_ASSIGNMENT','ASSIGNED'], true))
                <form method="POST" action="{{ route('store.orders.cancel', $order) }}">
                    @csrf
                    <x-store.button variant="danger" type="submit">
                        <i class="fa-solid fa-ban"></i> Annuler
                    </x-store.button>
                </form>
            @endif
        </div>
    </div>

    <div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-store.order-timeline :status="$order->status" />

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h3 class="text-lg font-extrabold">Articles</h3>

                <div class="mt-4 divide-y divide-gray-100">
                    @foreach($order->items as $it)
                        <div class="py-4 flex items-center justify-between">
                            <div>
                                <div class="font-bold text-gray-900">{{ $it->name }}</div>
                                <div class="text-sm text-gray-600 mt-1">
                                    {{ number_format((int)$it->unit_price, 0, ',', ' ') }} FCFA × {{ (int)$it->quantity }}
                                </div>
                            </div>
                            <div class="font-extrabold text-gray-900">
                                {{ number_format((int)$it->line_total, 0, ',', ' ') }} FCFA
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h3 class="text-lg font-extrabold">Livraison</h3>
                <div class="mt-3 text-sm text-gray-700 space-y-2">
                    <div><span class="text-gray-500">Téléphone:</span> <b>{{ $order->phone }}</b></div>
                    <div><span class="text-gray-500">Adresse:</span> <b>{{ $order->delivery_address }}</b></div>
                    @if($order->notes)
                        <div><span class="text-gray-500">Note:</span> <b>{{ $order->notes }}</b></div>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h3 class="text-lg font-extrabold">Paiement</h3>
                <div class="mt-3 text-sm text-gray-700">
                    @php
                        $pm = match($order->payment_method) {
                            'momo' => 'Mobile Money',
                            'card' => 'Carte',
                            default => 'Espèces'
                        };
                    @endphp
                    Méthode: <b>{{ $pm }}</b>
                </div>

                <div class="mt-4 border-t border-gray-100 pt-4 space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>Sous-total</span>
                        <span class="font-semibold text-gray-900">{{ number_format((int)$order->subtotal, 0, ',', ' ') }} FCFA</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Livraison</span>
                        <span class="font-semibold text-gray-900">{{ number_format((int)$order->delivery_fee, 0, ',', ' ') }} FCFA</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-bold text-gray-900">Total</span>
                        <span class="font-extrabold text-blue-700">{{ number_format((int)$order->total, 0, ',', ' ') }} FCFA</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
