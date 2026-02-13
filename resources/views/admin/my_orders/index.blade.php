@extends('layouts.admin')

@section('title','E-PHARMA - Mes livraisons')
@section('page_title','Mes livraisons')

@section('content')
@php
    $status = $status ?? request('status');

    use App\Enums\OrderStatus;

    $badge = fn($s) => match($s){
        'PENDING_ASSIGNMENT' => 'yellow',
        'ASSIGNED' => 'blue',
        'IN_DELIVERY' => 'indigo',
        'ACCEPTED' => 'orange',
        'DELIVERED' => 'green',
        'REFUSED' => 'red',
        'CANCELED' => 'red',
        default => 'gray',
    };


    $label = fn($s) => match($s){
        'PENDING_ASSIGNMENT' => 'En attente livreur',
        'ASSIGNED' => 'Affectée',
        'IN_DELIVERY' => 'En livraison',
        'ACCEPTED' => 'Acceptée',
        'DELIVERED' => 'Livrée',
        'REFUSED' => 'Refusée',
        'CANCELED' => 'Annulée',
        default => $s,
    };

    $statusOptions = [
    OrderStatus::PENDING_ASSIGNMENT->value => 'En attente livreur',
    OrderStatus::ASSIGNED->value => 'Affectée',
    OrderStatus::IN_DELIVERY->value => 'En livraison',
    OrderStatus::DELIVERED->value => 'Livrée',
    OrderStatus::CANCELED->value => 'Annulée',
];
@endphp

@if(session('success'))
    <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">
        {{ session('success') }}
    </div>
@endif

<x-admin.card title="Mes livraisons" class="p-0">
    <x-slot:actions>
        <form method="GET" class="flex gap-2 items-center">
            <x-admin.select name="status">
                @foreach($statusOptions as $val => $txt)
                    <option value="{{ $val }}" {{ (string)$status === (string)$val ? 'selected' : '' }}>
                        {{ $txt }}
                    </option>
                @endforeach
            </x-admin.select>
            <x-admin.button type="submit" variant="outline" icon="fa-solid fa-magnifying-glass">Filtrer</x-admin.button>
        </form>
    </x-slot:actions>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Commande</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Adresse</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-200">
            @forelse($orders as $o)
                <tr>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">#EP-{{ $o->id }}</td>
                    <td class="px-6 py-4 text-sm text-gray-700">{{ $o->user?->name ?? 'Client' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-700">{{ \Illuminate\Support\Str::limit($o->delivery_address, 35) }}</td>
                    <td class="px-6 py-4 text-sm text-gray-700">{{ number_format((int)$o->total_amount,0,',',' ') }} FCFA</td>
                    <td class="px-6 py-4">
                <x-admin.badge :text="$label($o->status)" :variant="$badge($o->status)" />
                    </td>
                    <td class="px-6 py-4 text-sm flex gap-3 items-center">
                        <a href="{{ route('courier.my_orders.show',$o) }}" class="text-primary hover:text-secondary" title="Voir">
                            <i class="fa-regular fa-eye"></i>
                        </a>

                        @if($o->assignment?->status === 'assigned')
                            <form method="POST" action="{{ route('courier.my_orders.accept',$o) }}">
                                @csrf @method('PATCH')
                                <button class="text-green-700 hover:opacity-80" title="Accepter">
                                    <i class="fa-solid fa-circle-check"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('courier.my_orders.refuse',$o) }}">
                                @csrf @method('PATCH')
                                <button class="text-danger hover:opacity-80" title="Refuser">
                                    <i class="fa-solid fa-circle-xmark"></i>
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-6 py-10">
                    <x-admin.empty-state title="Aucune livraison" description="Aucune commande assignée pour le moment." icon="fa-solid fa-inbox" />
                </td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <x-admin.pagination :paginator="$orders" />
</x-admin.card>
@endsection
