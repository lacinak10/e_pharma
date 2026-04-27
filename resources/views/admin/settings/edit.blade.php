@extends('layouts.admin')

@section('title','E-PHARMA - Paramètres')
@section('page_title','Paramètres')

@section('content')
@if(session('success'))
    <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">
        {{ session('success') }}
    </div>
@endif

<x-admin.card title="Paramètres">
    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Langue</label>
            <x-admin.select name="language">
                <option value="fr" {{ ($settings['language'] ?? 'fr') === 'fr' ? 'selected' : '' }}>Français</option>
                <option value="en" {{ ($settings['language'] ?? 'fr') === 'en' ? 'selected' : '' }}>English</option>
            </x-admin.select>
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" name="notifications" value="1"
                   {{ ($settings['notifications'] ?? true) ? 'checked' : '' }}
                   class="h-4 w-4 border-gray-300 rounded">
            <span class="text-sm text-gray-700">Activer les notifications</span>
        </div>

        <div class="pt-4 border-t flex justify-end">
            <x-admin.button type="submit" variant="primary" icon="fa-solid fa-floppy-disk">
                Enregistrer
            </x-admin.button>
        </div>
    </form>

    <p class="text-xs text-gray-500 mt-3">
        Note: ces paramètres sont stockés en session (tu peux ensuite les persister en base si tu veux).
    </p>
</x-admin.card>
@endsection
