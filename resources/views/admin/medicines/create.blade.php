@extends('layouts.app')

@section('content')
<h1 class="text-xl font-bold mb-4">Créer un médicament</h1>

<div class="bg-white rounded border p-4">
    <form method="POST" action="{{ route('manager.medicines.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @csrf

        <div class="md:col-span-2">
            <label class="block text-sm text-gray-600 mb-1">Nom *</label>
            <input type="text" name="name" value="{{ old('name') }}" class="border rounded px-3 py-2 w-full" required>
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm text-gray-600 mb-1">Description</label>
            <textarea name="description" rows="4" class="border rounded px-3 py-2 w-full">{{ old('description') }}</textarea>
        </div>

        <div>
            <label class="block text-sm text-gray-600 mb-1">Prix (FCFA) *</label>
            <input type="number" min="0" name="price" value="{{ old('price', 0) }}"
                   class="border rounded px-3 py-2 w-full" required>
        </div>

        <div>
            <label class="block text-sm text-gray-600 mb-1">Stock *</label>
            <input type="number" min="0" name="stock" value="{{ old('stock', 0) }}"
                   class="border rounded px-3 py-2 w-full" required>
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm text-gray-600 mb-1">Image URL</label>
            <input type="url" name="image_url" value="{{ old('image_url') }}" class="border rounded px-3 py-2 w-full">
        </div>

        <div class="md:col-span-2 flex items-center gap-2">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
            <label for="is_active" class="text-sm">Actif</label>
        </div>

        <div class="md:col-span-2 flex items-center justify-between">
            <a class="underline" href="{{ route('manager.medicines.index') }}">Retour</a>
            <button class="px-4 py-2 rounded bg-green-700 text-white" type="submit">Enregistrer</button>
        </div>
    </form>
</div>
@endsection
