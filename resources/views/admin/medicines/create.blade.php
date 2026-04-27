@extends('layouts.admin')

@section('title', 'E-PHARMA - Créer un produit')
@section('page_title', 'Créer un médicament')

@section('content')
@if($errors->any())
    <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
        <div class="font-semibold mb-1">Erreurs :</div>
        <ul class="list-disc pl-5">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<x-admin.card title="Informations produit">
    <form method="POST" action="{{ route('manager.medicines.store') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nom du produit *</label>
                <x-admin.input name="name" value="{{ old('name') }}" placeholder="Ex: Paracétamol 500mg" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Référence (SKU) *</label>
                <x-admin.input name="reference" value="{{ old('reference') }}" placeholder="Ex: MED-001" />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie *</label>
                <x-admin.select name="category_id">
                    <option value="">Sélectionner une catégorie</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ (int)old('category_id') === (int)$cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </x-admin.select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Prix (FCFA) *</label>
                <x-admin.input name="price" type="number" min="0" value="{{ old('price', 0) }}" />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Stock initial *</label>
                <x-admin.input name="stock" type="number" min="0" value="{{ old('stock', 0) }}" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Seuil d'alerte *</label>
                <x-admin.input name="alert_threshold" type="number" min="0" value="{{ old('alert_threshold', 15) }}" />
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea name="description" rows="4"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">{{ old('description') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Image du produit (optionnel)</label>
            <input type="file" name="image_url" accept="image/*"
                   class="w-full text-sm text-gray-700 file:mr-3 file:px-4 file:py-2 file:rounded-lg file:border-0
                          file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200" />
            <p class="text-xs text-gray-500 mt-1">Formats acceptés : jpg, jpeg, png, webp. Max 2MB.</p>
        </div>

        <div class="flex items-center gap-2">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300">
            <label for="is_active" class="text-sm font-medium text-gray-700">Actif (visible en boutique)</label>
        </div>

        <div class="pt-4 border-t border-gray-200 flex items-center justify-between">
            <a href="{{ route('manager.medicines.index') }}"
               class="px-4 py-2 rounded-lg border bg-white text-gray-700 hover:bg-gray-50">
                ← Retour
            </a>
            <x-admin.button type="submit" variant="primary" icon="fa-solid fa-floppy-disk">
                Enregistrer
            </x-admin.button>
        </div>
    </form>
</x-admin.card>
@endsection
