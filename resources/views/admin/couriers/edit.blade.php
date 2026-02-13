@extends('layouts.admin')

@section('title', 'E-PHARMA - Modifier livreur')
@section('page_title', 'Modifier livreur')

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

<x-admin.card title="Informations livreur">
    <form method="POST" action="{{ route('manager.couriers.update', $courier) }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                <x-admin.input name="name" value="{{ old('name', $courier->name) }}" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                <x-admin.input name="phone" value="{{ old('phone', $courier->phone) }}" />
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <x-admin.input name="email" type="email" value="{{ old('email', $courier->email) }}" />
        </div>

        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $courier->is_active) ? 'checked' : '' }}>
            Actif
        </label>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nouveau mot de passe (optionnel)</label>
                <x-admin.input name="password" type="password" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirmer</label>
                <x-admin.input name="password_confirmation" type="password" />
            </div>
        </div>

        <div class="pt-4 border-t flex justify-end gap-2">
            <a href="{{ route('manager.couriers.show', $courier) }}"
               class="px-4 py-2 rounded-lg border bg-white text-gray-700 hover:bg-gray-50">
                Retour
            </a>
            <x-admin.button type="submit" variant="primary" icon="fa-solid fa-floppy-disk">
                Mettre à jour
            </x-admin.button>
        </div>
    </form>
</x-admin.card>
@endsection
