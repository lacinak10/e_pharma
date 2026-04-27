@extends('layouts.admin')

@section('title','E-PHARMA - Ajuster stock')
@section('page_title','Ajuster stock')

@section('content')
<x-admin.card title="Ajuster stock">
    <div class="mb-4 text-sm text-gray-500">
        Produit: <span class="font-semibold text-gray-800">{{ $medicine->name }}</span>
    </div>

    <form method="POST" action="{{ route('manager.stock.update',$medicine) }}" class="space-y-4">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Stock</label>
                <x-admin.input type="number" min="0" name="stock" value="{{ old('stock',$medicine->stock) }}" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Seuil d’alerte</label>
                <x-admin.input type="number" min="0" name="alert_threshold" value="{{ old('alert_threshold',$medicine->alert_threshold) }}" />
            </div>
        </div>

        <div class="pt-4 border-t flex justify-end gap-2">
            <a href="{{ route('manager.stock.index') }}" class="px-4 py-2 rounded-lg border bg-white text-gray-700 hover:bg-gray-50">Retour</a>
            <x-admin.button type="submit" variant="primary">Enregistrer</x-admin.button>
        </div>
    </form>
</x-admin.card>
@endsection
