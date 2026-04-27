@extends('layouts.admin')

@section('title','E-PHARMA - Mon profil')
@section('page_title','Mon profil')

@section('content')
@if(session('success'))
    <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">
        {{ session('success') }}
    </div>
@endif

<x-admin.card title="Mon profil">
    <form method="POST" action="{{ route('admin.profile.update') }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                <x-admin.input name="name" value="{{ old('name',$user->name) }}" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <x-admin.input name="email" type="email" value="{{ old('email',$user->email) }}" />
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nouveau mot de passe (optionnel)</label>
            <x-admin.input name="password" type="password" placeholder="Min 8 caractères" />
            <p class="text-xs text-gray-500 mt-1">Laisse vide si tu ne veux pas changer le mot de passe.</p>
        </div>

        <div class="pt-4 border-t flex justify-end">
            <x-admin.button type="submit" variant="primary" icon="fa-solid fa-floppy-disk">
                Enregistrer
            </x-admin.button>
        </div>
    </form>
</x-admin.card>
@endsection
