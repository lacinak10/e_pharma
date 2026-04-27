@extends('layouts.admin')

@section('title','E-PHARMA - Catégories')
@section('page_title','Catégories')

@section('content')
@php
    $q = $q ?? request('q');
@endphp

<x-admin.card title="Catégories" class="p-0">
    <x-slot:actions>
        <div class="flex gap-2">
            <form method="GET" class="flex gap-2">
                <x-admin.input name="q" value="{{ $q }}" placeholder="Rechercher une catégorie..." />
                <x-admin.button type="submit" variant="outline" icon="fa-solid fa-magnifying-glass">Filtrer</x-admin.button>
            </form>

            <x-admin.button type="button" variant="primary" icon="fa-solid fa-plus" data-modal-open="addCategoryModal">
                Ajouter
            </x-admin.button>
        </div>
    </x-slot:actions>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Slug</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($categories as $c)
                    <tr>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $c->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $c->slug }}</td>
                        <td class="px-6 py-4">
                            <x-admin.badge :text="$c->is_active ? 'Active' : 'Inactive'" :variant="$c->is_active ? 'green' : 'red'" />
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 flex gap-3">
                            <a href="{{ route('manager.categories.edit',$c) }}" class="text-primary hover:text-secondary" title="Modifier">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </a>
                            <form method="POST" action="{{ route('manager.categories.destroy',$c) }}" onsubmit="return confirm('Désactiver cette catégorie ?')">
                                @csrf @method('DELETE')
                                <button class="text-danger hover:opacity-80" title="Désactiver">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-6 py-10">
                        <x-admin.empty-state title="Aucune catégorie" description="Crée ta première catégorie." icon="fa-solid fa-tags" />
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-admin.pagination :paginator="$categories" />
</x-admin.card>
@endsection

@push('modals')
<x-admin.modal id="addCategoryModal" title="Ajouter une catégorie">
    <form method="POST" action="{{ route('manager.categories.store') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
            <x-admin.input name="name" placeholder="Ex: Antibiotique" />
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" checked class="h-4 w-4 border-gray-300 rounded">
            <span class="text-sm text-gray-700">Active</span>
        </div>

        <div class="pt-4 border-t flex justify-end gap-2">
            <x-admin.button type="button" variant="outline" data-modal-close="addCategoryModal">Annuler</x-admin.button>
            <x-admin.button type="submit" variant="primary">Enregistrer</x-admin.button>
        </div>
    </form>
</x-admin.modal>
@endpush
