@extends('layouts.admin')

@section('title','E-PHARMA - Détail produit')
@section('page_title','Détail produit')

@section('content')
@if(session('success'))
    <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">
        {{ session('success') }}
    </div>
@endif

@php
    $img = $medicine->image_url
        ? (str_starts_with($medicine->image_url, 'http') ? $medicine->image_url : asset('storage/'.$medicine->image_url))
        : null;

    $stockVariant = $medicine->stock <= 0 ? 'red' : ($medicine->stock <= $medicine->alert_threshold ? 'yellow' : 'green');
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- Bloc principal --}}
    <x-admin.card title="Informations produit" class="lg:col-span-2">
        <div class="flex items-start gap-4">
            <div class="shrink-0">
                @if($img)
                    <img src="{{ $img }}" class="w-24 h-24 rounded-xl object-cover border" alt="Image produit">
                @else
                    <div class="w-24 h-24 rounded-xl bg-gray-50 border flex items-center justify-center text-gray-400">
                        <i class="fa-regular fa-image text-xl"></i>
                    </div>
                @endif
            </div>

            <div class="flex-1">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-xs text-gray-500">Nom</div>
                        <div class="text-xl font-bold text-gray-900">{{ $medicine->name }}</div>
                        <div class="text-sm text-gray-500 mt-1">SKU: <span class="font-semibold text-gray-700">{{ $medicine->reference }}</span></div>
                    </div>

                    <div class="text-right space-y-2">
                        <x-admin.badge :text="$medicine->is_active ? 'Actif' : 'Inactif'" :variant="$medicine->is_active ? 'green' : 'red'" />
                        <x-admin.badge :text="$medicine->status" :variant="$stockVariant" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-4">
                    <div class="p-3 rounded-lg bg-gray-50 border border-gray-100">
                        <div class="text-xs text-gray-500">Catégorie</div>
                        <div class="text-sm font-semibold text-gray-900">{{ $medicine->category?->name ?? '—' }}</div>
                    </div>
                    <div class="p-3 rounded-lg bg-gray-50 border border-gray-100">
                        <div class="text-xs text-gray-500">Prix</div>
                        <div class="text-sm font-bold text-gray-900">{{ number_format((int)$medicine->price,0,',',' ') }} FCFA</div>
                    </div>
                    <div class="p-3 rounded-lg bg-gray-50 border border-gray-100">
                        <div class="text-xs text-gray-500">Stock</div>
                        <div class="text-sm font-bold text-gray-900">{{ $medicine->stock }}</div>
                    </div>
                    <div class="p-3 rounded-lg bg-gray-50 border border-gray-100">
                        <div class="text-xs text-gray-500">Seuil d’alerte</div>
                        <div class="text-sm font-bold text-gray-900">{{ $medicine->alert_threshold }}</div>
                    </div>
                </div>
            </div>
        </div>

        @if($medicine->description)
            <div class="mt-5 p-4 rounded-lg bg-gray-50 border border-gray-100">
                <div class="text-sm font-semibold text-gray-800 mb-1">Description</div>
                <div class="text-sm text-gray-700 leading-relaxed">
                    {{ $medicine->description }}
                </div>
            </div>
        @endif

        <div class="pt-4 border-t mt-6 flex flex-col md:flex-row gap-2 md:justify-between md:items-center">
            <a href="{{ route('manager.medicines.index') }}" class="text-sm text-gray-700 hover:text-gray-900">
                ← Retour à la liste
            </a>

            <div class="flex gap-2">
                <a href="{{ route('manager.medicines.edit',$medicine) }}"
                   class="px-4 py-2 rounded-lg bg-primary text-white hover:bg-secondary text-sm inline-flex items-center gap-2">
                    <i class="fa-regular fa-pen-to-square"></i>
                    Modifier
                </a>

                <form method="POST" action="{{ route('manager.medicines.destroy',$medicine) }}"
                      onsubmit="return confirm('Supprimer ce produit ?')">
                    @csrf @method('DELETE')
                    <button class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 text-sm inline-flex items-center gap-2">
                        <i class="fa-regular fa-trash-can"></i>
                        Supprimer
                    </button>
                </form>
            </div>
        </div>
    </x-admin.card>

    {{-- Actions rapides --}}
    <x-admin.card title="Actions rapides">
        <div class="space-y-3">
            <div class="p-3 rounded-lg bg-gray-50 border border-gray-100">
                <div class="text-xs text-gray-500 mb-1">Activation</div>

                <form method="POST" action="{{ route('manager.medicines.toggle',$medicine) }}">
                    @csrf @method('PATCH')
                    <button class="w-full px-4 py-2 rounded-lg text-sm font-medium
                        {{ $medicine->is_active ? 'bg-red-50 text-red-700 border border-red-200 hover:bg-red-100' : 'bg-green-50 text-green-700 border border-green-200 hover:bg-green-100' }}">
                        {{ $medicine->is_active ? 'Désactiver le produit' : 'Activer le produit' }}
                    </button>
                </form>

                <p class="text-xs text-gray-500 mt-2">
                    Désactiver masque le produit dans le catalogue sans le supprimer.
                </p>
            </div>

            <div class="p-3 rounded-lg bg-gray-50 border border-gray-100">
                <div class="text-xs text-gray-500 mb-1">Statut stock</div>
                <div class="text-sm text-gray-700">
                    {{ $medicine->stock <= 0 ? 'Rupture : réapprovisionner rapidement.' : ($medicine->stock <= $medicine->alert_threshold ? 'Stock faible : prévoir un réassort.' : 'OK : stock normal.') }}
                </div>
            </div>
        </div>
    </x-admin.card>

</div>
@endsection
