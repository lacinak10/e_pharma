@extends('layouts.admin')

@section('title', 'E-PHARMA - Livreurs')
@section('page_title', 'Livreurs')

@section('content')
@if(session('success'))
    <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">
        {{ session('success') }}
    </div>
@endif

@php
    $filters = $filters ?? ['q' => request('q'), 'status' => request('status')];
@endphp

<x-admin.card title="Livreurs" class="p-0">
    <x-slot:actions>
        <div class="flex gap-2">
            <a href="{{ route('manager.couriers.create') }}"
               class="px-4 py-2 rounded-lg bg-primary text-white hover:bg-secondary text-sm inline-flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> Ajouter
            </a>
        </div>
    </x-slot:actions>

    <div class="p-4 border-b">
        <form method="GET" action="{{ route('manager.couriers.index') }}" class="flex flex-col md:flex-row gap-2 md:items-center">
            <div class="flex-1">
                <x-admin.input name="q" value="{{ $filters['q'] }}" placeholder="Nom, email, téléphone..." />
            </div>

            <div class="w-full md:w-56">
                <x-admin.select name="status">
                    <option value="">Tous</option>
                    <option value="active" {{ $filters['status']==='active' ? 'selected' : '' }}>Actifs</option>
                    <option value="inactive" {{ $filters['status']==='inactive' ? 'selected' : '' }}>Inactifs</option>
                </x-admin.select>
            </div>

            <x-admin.button type="submit" variant="outline" icon="fas fa-search">Filtrer</x-admin.button>

            <a href="{{ route('manager.couriers.index') }}"
               class="px-4 py-2 rounded-lg border bg-white text-gray-700 hover:bg-gray-50 text-sm">
                Reset
            </a>
        </form>
    </div>

    <x-admin.table>
        <x-slot:head>
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Livreur</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Assignées</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">En cours</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Livrées</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Refusées</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </x-slot:head>

        @forelse($couriers as $c)
            @php
                $st = $statsByCourier->get($c->id);
                $assigned = (int)($st->assigned ?? 0);
                $inProgress = (int)($st->in_progress ?? 0);
                $delivered = (int)($st->delivered ?? 0);
                $refused = (int)($st->refused ?? 0);
            @endphp

            <tr class="border-t">
                <td class="px-6 py-4">
                    <div class="font-semibold text-gray-900">{{ $c->name }}</div>
                    <div class="text-xs text-gray-500">#{{ $c->id }}</div>
                </td>

                <td class="px-6 py-4 text-sm text-gray-700">
                    <div>{{ $c->email }}</div>
                    <div class="text-xs text-gray-500">{{ $c->phone ?? '—' }}</div>
                </td>

                <td class="px-6 py-4">
                    <x-admin.badge :text="$c->is_active ? 'Actif' : 'Inactif'" :variant="$c->is_active ? 'green' : 'red'" />
                </td>

                <td class="px-6 py-4"><x-admin.badge :text="$assigned" variant="blue" /></td>
                <td class="px-6 py-4"><x-admin.badge :text="$inProgress" variant="indigo" /></td>
                <td class="px-6 py-4"><x-admin.badge :text="$delivered" variant="green" /></td>
                <td class="px-6 py-4"><x-admin.badge :text="$refused" variant="red" /></td>

                <td class="px-6 py-4">
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('manager.couriers.show', $c) }}"
                           class="px-3 py-2 rounded-lg bg-gray-900 text-white hover:bg-gray-800 text-sm inline-flex items-center gap-2">
                            <i class="fa-regular fa-eye"></i> Détails
                        </a>

                        <a href="{{ route('manager.couriers.edit', $c) }}"
                           class="px-3 py-2 rounded-lg border bg-white text-gray-700 hover:bg-gray-50 text-sm inline-flex items-center gap-2">
                            <i class="fa-regular fa-pen-to-square"></i> Modifier
                        </a>

                       

                        <form method="POST" action="{{ route('manager.couriers.destroy', $c) }}"
                              onsubmit="return confirm('Supprimer ce livreur ?');">
                            @csrf @method('DELETE')
                            <button class="px-3 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 text-sm inline-flex items-center gap-2">
                                <i class="fa-regular fa-trash-can"></i> Supprimer
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="px-6 py-10">
                    <x-admin.empty-state title="Aucun livreur" description="Aucun livreur trouvé." icon="fas fa-truck" />
                </td>
            </tr>
        @endforelse
    </x-admin.table>

    <x-admin.pagination :paginator="$couriers" />
</x-admin.card>
@endsection
