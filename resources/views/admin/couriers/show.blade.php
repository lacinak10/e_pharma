@extends('layouts.admin')

@section('title', 'E-PHARMA - Détail livreur')
@section('page_title', 'Détail livreur')

@section('content')
@if(session('success'))
    <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">
        {{ session('success') }}
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    <x-admin.card title="Informations livreur" class="lg:col-span-2">
        <div class="flex items-start justify-between gap-3">
            <div>
                <div class="text-xs text-gray-500">Nom</div>
                <div class="text-xl font-bold text-gray-900">{{ $courier->name }}</div>
                <div class="text-sm text-gray-500 mt-1">Email: <span class="font-semibold text-gray-700">{{ $courier->email }}</span></div>
                <div class="text-sm text-gray-500 mt-1">Téléphone: <span class="font-semibold text-gray-700">{{ $courier->phone ?? '—' }}</span></div>
            </div>

            <div class="text-right space-y-2">
                <x-admin.badge text="Courier" variant="indigo" />
                <x-admin.badge :text="$courier->is_active ? 'Actif' : 'Inactif'" :variant="$courier->is_active ? 'green' : 'red'" />
                <x-admin.badge :text="'Taux acceptation: '.$stats['acceptRate'].'%'" variant="green" />
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mt-5">
            <div class="p-3 rounded-lg bg-gray-50 border border-gray-100">
                <div class="text-xs text-gray-500">Assignées</div>
                <div class="text-lg font-bold text-gray-900">{{ $stats['assigned'] }}</div>
            </div>
            <div class="p-3 rounded-lg bg-gray-50 border border-gray-100">
                <div class="text-xs text-gray-500">Acceptées</div>
                <div class="text-lg font-bold text-gray-900">{{ $stats['accepted'] }}</div>
            </div>
            <div class="p-3 rounded-lg bg-gray-50 border border-gray-100">
                <div class="text-xs text-gray-500">En livraison</div>
                <div class="text-lg font-bold text-gray-900">{{ $stats['delivering'] }}</div>
            </div>
            <div class="p-3 rounded-lg bg-gray-50 border border-gray-100">
                <div class="text-xs text-gray-500">Livrées</div>
                <div class="text-lg font-bold text-gray-900">{{ $stats['delivered'] }}</div>
            </div>
            <div class="p-3 rounded-lg bg-gray-50 border border-gray-100">
                <div class="text-xs text-gray-500">Refusées</div>
                <div class="text-lg font-bold text-gray-900">{{ $stats['refused'] }}</div>
            </div>
            <div class="p-3 rounded-lg bg-gray-50 border border-gray-100">
                <div class="text-xs text-gray-500">Total</div>
                <div class="text-lg font-bold text-gray-900">{{ $stats['total'] }}</div>
            </div>
        </div>

        <div class="pt-4 border-t mt-6 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
            <a href="{{ route('manager.couriers.index') }}" class="text-sm text-gray-700 hover:text-gray-900">
                ← Retour à la liste
            </a>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('manager.couriers.edit', $courier) }}"
                   class="px-4 py-2 rounded-lg border bg-white text-gray-700 hover:bg-gray-50 text-sm inline-flex items-center gap-2">
                    <i class="fa-regular fa-pen-to-square"></i> Modifier
                </a>


                <form method="POST" action="{{ route('manager.couriers.destroy', $courier) }}"
                      onsubmit="return confirm('Supprimer ce livreur ?');">
                    @csrf @method('DELETE')
                    <button class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 text-sm inline-flex items-center gap-2">
                        <i class="fa-regular fa-trash-can"></i> Supprimer
                    </button>
                </form>
            </div>
        </div>
    </x-admin.card>

    <x-admin.card title="Résumé">
        <div class="p-3 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">
            La suppression est en <b>soft delete</b> (recommandé) pour garder l’historique des livraisons.
        </div>
    </x-admin.card>
</div>
@endsection
