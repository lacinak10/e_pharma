@extends('layouts.admin')

@php $isNew = ! $pharmacy->exists; @endphp

@section('title', ($isNew ? 'Nouvelle pharmacie' : $pharmacy->name) . ' — ePharma')
@section('page_title', $isNew ? 'Nouvelle pharmacie partenaire' : 'Modifier ' . $pharmacy->name)

@section('content')
    <form method="POST"
          action="{{ $isNew ? route('manager.pharmacies.store') : route('manager.pharmacies.update', $pharmacy) }}"
          style="max-width:720px">
        @csrf
        @unless($isNew) @method('PUT') @endunless

        <x-ep.card>
            <div class="ep-grid" style="grid-template-columns:repeat(auto-fit,minmax(min(100%,220px),1fr));gap:1rem">
                <div class="ep-field">
                    <label class="ep-label" for="name">Nom</label>
                    <input class="ep-input" id="name" name="name" required maxlength="120"
                           value="{{ old('name', $pharmacy->name) }}">
                    <x-input-error :messages="$errors->get('name')" class="ep-error" />
                </div>

                <div class="ep-field">
                    <label class="ep-label" for="area">Quartier</label>
                    <input class="ep-input" id="area" name="area" required maxlength="80"
                           value="{{ old('area', $pharmacy->area) }}" placeholder="Cocody Angré">
                    <x-input-error :messages="$errors->get('area')" class="ep-error" />
                </div>

                <div class="ep-field">
                    <label class="ep-label" for="phone">Téléphone</label>
                    <input class="ep-input" id="phone" name="phone" required maxlength="30"
                           value="{{ old('phone', $pharmacy->phone) }}" placeholder="+225 27 22 00 00 00">
                    <x-input-error :messages="$errors->get('phone')" class="ep-error" />
                </div>

                <div class="ep-field">
                    <label class="ep-label" for="address">Adresse</label>
                    <input class="ep-input" id="address" name="address" maxlength="200"
                           value="{{ old('address', $pharmacy->address) }}">
                </div>

                <div class="ep-field">
                    <label class="ep-label" for="latitude">Latitude</label>
                    <input class="ep-input ep-mono" id="latitude" name="latitude" type="number" step="0.0000001"
                           value="{{ old('latitude', $pharmacy->latitude) }}" placeholder="5.3244">
                    <p class="ep-hint">Sert à proposer le livreur le plus proche.</p>
                </div>

                <div class="ep-field">
                    <label class="ep-label" for="longitude">Longitude</label>
                    <input class="ep-input ep-mono" id="longitude" name="longitude" type="number" step="0.0000001"
                           value="{{ old('longitude', $pharmacy->longitude) }}" placeholder="-4.0189">
                </div>

                <div class="ep-field">
                    <label class="ep-label" for="reliability">Fiabilité constatée (%)</label>
                    <input class="ep-input ep-mono" id="reliability" name="reliability" type="number" min="0" max="100" required
                           value="{{ old('reliability', $pharmacy->reliability ?? 100) }}">
                </div>

                <div class="ep-field">
                    <label class="ep-label" for="avg_response_minutes">Délai de réponse moyen (min)</label>
                    <input class="ep-input ep-mono" id="avg_response_minutes" name="avg_response_minutes"
                           type="number" min="1" max="60" required
                           value="{{ old('avg_response_minutes', $pharmacy->avg_response_minutes ?? 3) }}">
                </div>
            </div>

            <div class="ep-stack" style="gap:.5rem;margin-top:1.25rem">
                <label class="ep-choice">
                    <input type="checkbox" name="is_24h" value="1" @checked(old('is_24h', $pharmacy->is_24h))>
                    Ouverte 24 h/24
                </label>
                <label class="ep-choice">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $pharmacy->is_active ?? true))>
                    Partenaire active
                </label>
            </div>

            <x-slot:footer>
                <div class="ep-row">
                    <button type="submit" class="ep-btn ep-btn--primary">{{ $isNew ? 'Ajouter la pharmacie' : 'Enregistrer' }}</button>
                    <a href="{{ route('manager.pharmacies.index') }}" class="ep-btn ep-btn--ghost">Annuler</a>
                </div>
            </x-slot:footer>
        </x-ep.card>
    </form>
@endsection
