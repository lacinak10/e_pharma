@extends('layouts.admin')

@section('title', 'E-PHARMA - Modifier produit')
@section('page_title', 'Modifier un produit')

@section('content')
@if(session('success'))
    <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">
        {{ session('success') }}
    </div>
@endif

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

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- Formulaire --}}
    <x-admin.card title="Informations produit" class="lg:col-span-2">
        <form method="POST" action="{{ route('manager.medicines.update', $medicine) }}"
              enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                    <x-admin.input name="name" value="{{ old('name', $medicine->name) }}" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Référence (SKU)</label>
                    <x-admin.input name="reference" value="{{ old('reference', $medicine->reference) }}" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
                    <x-admin.select name="category_id">
                        <option value="">Sélectionner</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}"
                                {{ (int)old('category_id', $medicine->category_id) === (int)$cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </x-admin.select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Prix (FCFA)</label>
                    <x-admin.input name="price" type="number" min="0" value="{{ old('price', $medicine->price) }}" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stock</label>
                    <x-admin.input name="stock" type="number" min="0" value="{{ old('stock', $medicine->stock) }}" />
                    <p class="text-xs text-gray-500 mt-1">Le statut sera recalculé automatiquement.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Seuil d'alerte</label>
                    <x-admin.input name="alert_threshold" type="number" min="0"
                                 value="{{ old('alert_threshold', $medicine->alert_threshold) }}" />
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="4"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">{{ old('description', $medicine->description) }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nouvelle image (optionnel)</label>
                    <input type="file" name="image_url" accept="image/*"
                           class="w-full text-sm text-gray-700 file:mr-3 file:px-4 file:py-2 file:rounded-lg file:border-0
                                  file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200" />
                    <p class="text-xs text-gray-500 mt-1">Laisse vide si tu ne changes pas l’image.</p>
                </div>

                <div class="p-3 rounded-lg bg-gray-50 border border-gray-100">
                    <div class="text-xs text-gray-500 mb-2">Aperçu</div>
                    @php
                        $img = $medicine->image_url
                            ? (str_starts_with($medicine->image_url, 'http') ? $medicine->image_url : asset('storage/'.$medicine->image_url))
                            : null;
                    @endphp

                    @if($img)
                        <img src="{{ $img }}" alt="Image produit" class="w-24 h-24 rounded-lg object-cover border">
                    @else
                        <div class="w-24 h-24 rounded-lg bg-white border flex items-center justify-center text-gray-400">
                            <i class="fa-regular fa-image"></i>
                        </div>
                    @endif

                    <div class="mt-2 text-xs text-gray-500">
                        Statut actuel:
                        <span class="font-semibold text-gray-700">{{ $medicine->status }}</span>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-200 flex justify-end gap-2">
                <a href="{{ route('manager.medicines.index') }}"
                   class="px-4 py-2 rounded-lg border bg-white text-gray-700 hover:bg-gray-50">
                    Retour
                </a>

                <x-admin.button type="submit" variant="primary" icon="fa-solid fa-floppy-disk">
                    Mettre à jour
                </x-admin.button>
            </div>
        </form>
    </x-admin.card>

    {{-- Résumé / UX --}}
    <x-admin.card title="Résumé" class="lg:col-span-1">
        <div class="space-y-3">
            <div class="p-3 rounded-lg bg-gray-50 border border-gray-100">
                <div class="text-xs text-gray-500">Produit</div>
                <div class="text-sm font-semibold text-gray-900">{{ $medicine->name }}</div>
                <div class="text-xs text-gray-500 mt-1">SKU: {{ $medicine->reference }}</div>
            </div>

            <div class="p-3 rounded-lg bg-gray-50 border border-gray-100">
                <div class="text-xs text-gray-500">Prix</div>
                <div class="text-lg font-bold text-gray-900">{{ number_format((int)$medicine->price,0,',',' ') }} FCFA</div>
            </div>

            <div class="p-3 rounded-lg bg-gray-50 border border-gray-100">
                <div class="text-xs text-gray-500">Stock / Seuil</div>
                <div class="text-lg font-bold text-gray-900">{{ $medicine->stock }} / {{ $medicine->alert_threshold }}</div>
                <p class="text-xs text-gray-500 mt-1">Conseil: garde un seuil réaliste pour éviter les ruptures.</p>
            </div>

            <div class="p-3 rounded-lg bg-gray-50 border border-gray-100">
                <div class="text-xs text-gray-500">Statut</div>
                <div class="text-sm font-semibold text-gray-900">{{ $medicine->status }}</div>
            </div>
        </div>
    </x-admin.card>
</div>
@endsection
