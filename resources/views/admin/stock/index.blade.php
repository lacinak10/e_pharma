@extends('layouts.admin')

@section('title','E-PHARMA - Stock')
@section('page_title','Stock')

@section('content')
<x-admin.card title="Stock médicaments" class="p-0">
    <x-slot:actions>
        <form method="GET" class="flex gap-2">
            <x-admin.input name="q" value="{{ $q ?? request('q') }}" placeholder="Rechercher un produit..." />
            <x-admin.button type="submit" variant="outline" icon="fa-solid fa-magnifying-glass">Filtrer</x-admin.button>
        </form>
    </x-slot:actions>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produit</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Catégorie</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Seuil</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($medicines as $m)
                    @php
                        $variant = $m->stock <= 0 ? 'red' : ($m->stock <= $m->alert_threshold ? 'yellow' : 'green');
                    @endphp
                    <tr>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $m->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $m->category->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $m->stock }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $m->alert_threshold }}</td>
                        <td class="px-6 py-4">
                            <x-admin.badge :text="$m->status" :variant="$variant" />
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('manager.stock.edit',$m) }}" class="text-primary hover:text-secondary" title="Ajuster">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-10">
                        <x-admin.empty-state title="Aucun produit" description="Aucun médicament trouvé." icon="fa-solid fa-boxes-stacked" />
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-admin.pagination :paginator="$medicines" />
</x-admin.card>
@endsection
