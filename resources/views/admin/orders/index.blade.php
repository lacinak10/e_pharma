@extends('layouts.admin')

@section('title','E-PHARMA - Commandes')
@section('page_title','Commandes')

@section('content')
@php
use App\Enums\OrderStatus;

    $q = $q ?? request('q');
    $status = $status ?? request('status');

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
    OrderStatus::ACCEPTED->value => 'Acceptée',
    OrderStatus::DELIVERED->value => 'Livrée',
    OrderStatus::CANCELED->value => 'Annulée',
];
@endphp

@if(session('success'))
    <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">
        {{ session('success') }}
    </div>
@endif

<x-admin.card title="Liste des commandes" class="p-0">
    <x-slot:actions>
        <form method="GET" class="flex flex-col md:flex-row gap-2 md:items-center">
            <x-admin.input name="q" value="{{ $q }}" placeholder="Rechercher (client)..." />
            <x-admin.select name="status">
                @foreach($statusOptions as $val => $txt)
                    <option value="{{ $val }}" {{ (string)$status === (string)$val ? 'selected' : '' }}>
                        {{ $txt }}
                    </option>
                @endforeach
            </x-admin.select>
            <x-admin.button type="submit" variant="outline" icon="fa-solid fa-magnifying-glass">Filtrer</x-admin.button>

            @if($q || $status)
                <a href="{{ route('manager.orders.index') }}"
                   class="px-3 py-2 rounded-lg border bg-white text-gray-700 hover:bg-gray-50 text-sm">
                    Réinitialiser
                </a>
            @endif
        </form>
    </x-slot:actions>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">N°</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Livreur</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-200">
            @forelse($orders as $o)
                <tr>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">#EP-{{ $o->id }}</td>
                    <td class="px-6 py-4 text-sm text-gray-700">{{ $o->user->name ?? 'Client' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-700">
                        {{ $o->assignment?->courier?->name ?? '—' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ optional($o->created_at)->format('d/m/Y H:i') }}</td>
                    <td class="px-6 py-4 text-sm text-gray-700">
                        {{ number_format((int)$o->total_amount, 0, ',', ' ') }} FCFA
                    </td>
                    <td class="px-6 py-4">
                        <x-admin.badge :text="$label($o->status)" :variant="$badge($o->status)" />
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <a href="{{ route('manager.orders.show',$o) }}" class="text-primary hover:text-secondary" title="Voir">
                            <i class="fa-regular fa-eye"></i>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-10">
                        <x-admin.empty-state title="Aucune commande" description="Aucune commande ne correspond aux filtres." icon="fa-solid fa-clipboard-list" />
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <x-admin.pagination :paginator="$orders" />
</x-admin.card>
@endsection
