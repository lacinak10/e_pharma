@extends('layouts.admin')

@section('title', 'E-PHARMA - Créer un utilisateur')
@section('page_title', 'Créer un utilisateur')

@section('content')

@if(session('success'))
    <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm flex items-center gap-2">
        <i class="fa-solid fa-circle-check"></i>
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

<x-admin.card title="Informations de l'utilisateur">
    <form method="POST" action="{{ route('manager.users.store') }}" class="space-y-4">
        @csrf

        {{-- Nom & Prénom --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nom <span class="text-red-500">*</span></label>
                <x-admin.input name="name" value="{{ old('name') }}" placeholder="Nom de famille" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Prénom</label>
                <x-admin.input name="prenom" value="{{ old('prenom') }}" placeholder="Prénom" />
            </div>
        </div>

        {{-- Email & Téléphone --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                <x-admin.input name="email" type="email" value="{{ old('email') }}" placeholder="exemple@email.com" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                <x-admin.input name="phone" value="{{ old('phone') }}" placeholder="+225 XX XX XX XX XX" />
            </div>
        </div>

        {{-- Rôle --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Rôle <span class="text-red-500">*</span></label>
            <select name="role"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                <option value="">-- Choisir un rôle --</option>
                <option value="manager" {{ old('role') === 'manager' ? 'selected' : '' }}>Manager</option>
                <option value="courier" {{ old('role') === 'courier' ? 'selected' : '' }}>Livreur</option>
                <option value="client"  {{ old('role') === 'client'  ? 'selected' : '' }}>Client</option>
            </select>
        </div>

        {{-- Mot de passe --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe <span class="text-red-500">*</span></label>
                <x-admin.input name="password" type="password" placeholder="Minimum 8 caractères" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirmer le mot de passe <span class="text-red-500">*</span></label>
                <x-admin.input name="password_confirmation" type="password" placeholder="Répéter le mot de passe" />
            </div>
        </div>

        {{-- Statut --}}
        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}
                class="rounded border-gray-300 text-primary focus:ring-primary">
            Compte actif
        </label>

        <div class="pt-4 border-t flex justify-end gap-2">
            <x-admin.button type="submit" variant="primary" icon="fa-solid fa-user-plus">
                Créer l'utilisateur
            </x-admin.button>
        </div>
    </form>
</x-admin.card>

@endsection
