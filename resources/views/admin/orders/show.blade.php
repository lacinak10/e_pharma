@extends('layouts.admin')

@section('title','E-PHARMA - Détail commande')
@section('page_title','Détail commande')

@section('content')
@php
use App\Enums\OrderStatus;
use App\Enums\AssignmentStatus;

    $orderBadge = fn(OrderStatus $s) => match($s){
        OrderStatus::PENDING_ASSIGNMENT => 'yellow',
        OrderStatus::ASSIGNED           => 'blue',
        OrderStatus::ACCEPTED           => 'orange',
        OrderStatus::IN_DELIVERY        => 'indigo',
        OrderStatus::DELIVERED          => 'green',
        OrderStatus::REFUSED            => 'red',
        OrderStatus::CANCELED           => 'red',
        default                         => 'gray',
    };

    $statusOptions = [
        OrderStatus::PENDING_ASSIGNMENT->value => 'En attente livreur',
        OrderStatus::ASSIGNED->value           => 'Affectée',
        OrderStatus::ACCEPTED->value           => 'Acceptée',
        OrderStatus::IN_DELIVERY->value        => 'En livraison',
        OrderStatus::DELIVERED->value          => 'Livrée',
        OrderStatus::REFUSED->value            => 'Refusée',
        OrderStatus::CANCELED->value           => 'Annulée',
    ];
@endphp

@if(session('success'))
    <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">
        {{ session('success') }}
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- Colonne gauche: infos --}}
    <x-admin.card title="Informations" class="lg:col-span-2">
        <div class="flex items-start justify-between gap-3">
            <div>
                <div class="text-sm text-gray-500">Commande</div>
                <div class="text-2xl font-bold text-gray-900">#EP-{{ $order->id }}</div>
                <div class="text-sm text-gray-500 mt-1">
                    Créée: {{ optional($order->created_at)->format('d/m/Y H:i') }}
                </div>
            </div>
            <div>
                <x-admin.badge :text="$order->status->label()" :variant="$orderBadge($order->status)" />
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-4 rounded-lg bg-gray-50 border border-gray-100">
                <div class="text-xs text-gray-500">Client</div>
                <div class="text-sm font-semibold text-gray-800">{{ $order->user->name ?? 'Client' }}</div>
            </div>
            <div class="p-4 rounded-lg bg-gray-50 border border-gray-100">
                <div class="text-xs text-gray-500">Téléphone</div>
                <div class="text-sm font-semibold text-gray-800">{{ $order->delivery_phone ?? '—' }}</div>
            </div>
            <div class="p-4 rounded-lg bg-gray-50 border border-gray-100 md:col-span-2">
                <div class="text-xs text-gray-500">Adresse de livraison</div>
                <div class="text-sm font-semibold text-gray-800">{{ $order->delivery_address }}</div>
            </div>
            @if($order->notes)
            <div class="p-4 rounded-lg bg-gray-50 border border-gray-100 md:col-span-2">
                <div class="text-xs text-gray-500">Notes</div>
                <div class="text-sm text-gray-700">{{ $order->notes }}</div>
            </div>
            @endif
        </div>

        <div class="mt-6">
            <div class="text-sm font-semibold text-gray-800 mb-2">Articles</div>
            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produit</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qté</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">PU</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($order->items as $it)
                        <tr>
                            <td class="px-5 py-3 text-sm text-gray-800">{{ $it->medicine->name ?? 'Produit' }}</td>
                            <td class="px-5 py-3 text-sm text-gray-700">{{ $it->quantity }}</td>
                            <td class="px-5 py-3 text-sm text-gray-700">{{ number_format((int)$it->unit_price,0,',',' ') }} FCFA</td>
                            <td class="px-5 py-3 text-sm font-semibold text-gray-900">{{ number_format((int)$it->line_total,0,',',' ') }} FCFA</td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                    <tr>
                        <td class="px-5 py-3 text-sm text-gray-600" colspan="3">Sous-total</td>
                        <td class="px-5 py-3 text-sm font-semibold text-gray-900">{{ number_format((int)$order->subtotal,0,',',' ') }} FCFA</td>
                    </tr>
                    <tr>
                        <td class="px-5 py-3 text-sm text-gray-600" colspan="3">Frais livraison</td>
                        <td class="px-5 py-3 text-sm font-semibold text-gray-900">{{ number_format((int)$order->delivery_fee,0,',',' ') }} FCFA</td>
                    </tr>
                    <tr>
                        <td class="px-5 py-3 text-sm text-gray-900 font-semibold" colspan="3">Total</td>
                        <td class="px-5 py-3 text-sm font-bold text-gray-900">{{ number_format((int)$order->total_amount,0,',',' ') }} FCFA</td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </x-admin.card>

    {{-- Colonne droite: actions --}}
    <div class="space-y-4">

        <x-admin.card title="Affectation livreur">
            @if($order->assignment?->courier)
                <div class="p-3 rounded-lg bg-gray-50 border border-gray-100 mb-3">
                    <div class="text-xs text-gray-500">Livreur actuel</div>
                    <div class="text-sm font-semibold text-gray-900">{{ $order->assignment->courier->name }}</div>
                    <div class="text-xs text-gray-500 mt-1">Statut assignment: {{ $order->assignment->status->label() }}</div>
                </div>
            @endif

            <form method="POST" action="{{ route('manager.assignments.store') }}" class="space-y-3">
                @csrf
                <input type="hidden" name="order_id" value="{{ $order->id }}"/>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Choisir un livreur</label>
                    <x-admin.select name="courier_id">
                        <option value="">Sélectionner</option>
                        @foreach($couriers as $c)
                            <option value="{{ $c->id }}" {{ (int)old('courier_id') === (int)$c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </x-admin.select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Note (optionnel)</label>
                    <x-admin.input name="note" value="{{ old('note') }}" placeholder="Ex: Appeler avant d'arriver" />
                </div>

                <x-admin.button type="submit" variant="primary" icon="fa-solid fa-user-check">
                    Affecter
                </x-admin.button>

                <p class="text-xs text-gray-500 mt-2">
                    Astuce: après affectation, la commande passe en “Affectée”.
                </p>
            </form>
        </x-admin.card>

        <x-admin.card title="Statut commande">
            <form method="POST" action="{{ route('manager.orders.update',$order) }}" class="space-y-3">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Changer statut</label>
                    <x-admin.select name="status">
                        @foreach($statusOptions as $val => $txt)
                            <option value="{{ $val }}" {{ $order->status->value === $val ? 'selected' : '' }}>
                                {{ $txt }}
                            </option>
                        @endforeach
                    </x-admin.select>
                </div>

                <x-admin.button type="submit" variant="outline" icon="fa-solid fa-rotate">
                    Mettre à jour
                </x-admin.button>
            </form>

            <div class="pt-4 border-t mt-4 flex justify-between">
                <a href="{{ route('manager.orders.index') }}" class="text-sm text-gray-700 hover:text-gray-900">
                    ← Retour
                </a>
                <span class="text-xs text-gray-500">
                    Livrée le: {{ $order->delivered_at ? $order->delivered_at->format('d/m/Y H:i') : '—' }}
                </span>
            </div>
        </x-admin.card>

    </div>
</div>
@endsection
