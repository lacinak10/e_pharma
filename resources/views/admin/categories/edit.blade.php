@extends('layouts.admin')

@section('title', 'Modifier ' . $category->name . ' — ePharma')
@section('page_title', 'Modifier ' . $category->name)

@section('content')
    <form method="POST" action="{{ route('manager.categories.update', $category) }}" style="max-width:520px">
        @csrf @method('PUT')

        <x-ep.card>
            <div class="ep-field">
                <label class="ep-label" for="name">Nom</label>
                <input class="ep-input" id="name" name="name" required maxlength="255"
                       value="{{ old('name', $category->name) }}">
                <p class="ep-hint">Identifiant actuel : <span class="ep-mono">{{ $category->slug }}</span></p>
                <x-input-error :messages="$errors->get('name')" class="ep-error" />
            </div>

            <label class="ep-choice" style="margin-top:.875rem">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active))>
                Visible dans le catalogue client
            </label>

            <x-slot:footer>
                <div class="ep-row">
                    <button type="submit" class="ep-btn ep-btn--primary">Enregistrer</button>
                    <a href="{{ route('manager.categories.index') }}" class="ep-btn ep-btn--ghost">Annuler</a>
                </div>
            </x-slot:footer>
        </x-ep.card>
    </form>
@endsection
