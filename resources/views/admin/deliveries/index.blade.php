@extends('layouts.admin')

@section('title','E-PHARMA - Suivi livraisons')
@section('page_title','Suivi livraisons')

@section('content')
@php
    use App\Enums\OrderStatus;
    $status = $status ?? request('status', OrderStatus::IN_DELIVERY->value);

    $statusOptions = [
        OrderStatus::IN_DELIVERY->value        => 'En livraison',
        OrderStatus::DELIVERED->value          => 'Livrées',
        OrderStatus::ASSIGNED->value           => 'Affectées',
        OrderStatus::ACCEPTED->value           => 'Acceptées',
        OrderStatus::PENDING_ASSIGNMENT->value => 'En attente livreur',
        OrderStatus::CANCELED->value           => 'Annulées',
    ];

    $orderBadge = fn(OrderStatus $s) => match($s){
        OrderStatus::PENDING_ASSIGNMENT => 'yellow',
        OrderStatus::ASSIGNED           => 'blue',
        OrderStatus::ACCEPTED           => 'orange',
        OrderStatus::IN_DELIVERY        => 'indigo',
        OrderStatus::DELIVERED          => 'green',
        OrderStatus::CANCELED           => 'red',
        OrderStatus::REFUSED            => 'red',
        default                         => 'gray',
    };
@endphp

<x-admin.card title="Suivi des livraisons" class="p-0">
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
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Livreur</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Livrée le</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
            @forelse($orders as $o)
                <tr>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">#EP-{{ $o->id }}</td>
                    <td class="px-6 py-4 text-sm text-gray-700">{{ $o->user?->name ?? 'Client' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-700">{{ $o->assignment?->courier?->name ?? '—' }}</td>
                    <td class="px-6 py-4">
                        <x-admin.badge :text="$o->status->label()" :variant="$orderBadge($o->status)" />
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ $o->delivered_at ? $o->delivered_at->format('d/m/Y H:i') : '—' }}
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <a href="{{ route('manager.orders.show',$o) }}" class="text-primary hover:text-secondary" title="Voir">
                            <i class="fa-regular fa-eye"></i>
                        </a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-6 py-10">
                    <x-admin.empty-state title="Aucune livraison" description="Aucun résultat pour ce filtre." icon="fa-solid fa-truck-fast" />
                </td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <x-admin.pagination :paginator="$orders" />
</x-admin.card>
@endsection
