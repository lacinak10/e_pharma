@extends('layouts.admin')

@section('title','E-PHARMA - Clients')
@section('page_title','Clients')

@section('content')
@php $q = $q ?? request('q'); @endphp

<x-admin.card title="Clients" class="p-0">
    <x-slot:actions>
        <form method="GET" class="flex gap-2 items-center">
            <x-admin.input name="q" value="{{ $q }}" placeholder="Rechercher un client..." />
            <x-admin.button type="submit" variant="outline" icon="fa-solid fa-magnifying-glass">Filtrer</x-admin.button>

            @if($q)
                <a href="{{ route('manager.customers.index') }}"
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
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nb commandes</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
            @forelse($customers as $c)
                <tr>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $c->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-700">{{ $c->email }}</td>
                    <td class="px-6 py-4 text-sm text-gray-700">{{ $c->orders_count ?? 0 }}</td>
                    <td class="px-6 py-4 text-sm">
                        <a href="{{ route('manager.customers.show',$c) }}" class="text-primary hover:text-secondary" title="Voir">
                            <i class="fa-regular fa-eye"></i>
                        </a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-6 py-10">
                    <x-admin.empty-state title="Aucun client" description="Aucun client trouvé." icon="fa-solid fa-users" />
                </td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <x-admin.pagination :paginator="$customers" />
</x-admin.card>
@endsection
