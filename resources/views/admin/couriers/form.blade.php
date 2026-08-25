@extends('layouts.admin')

@php $isNew = ! $courier->exists; @endphp

@section('title', ($isNew ? 'Nouveau livreur' : $courier->name) . ' — ePharma')
@section('page_title', $isNew ? 'Ajouter un livreur' : 'Modifier ' . $courier->name)

@section('content')
    <form method="POST" style="max-width:760px"
          action="{{ $isNew ? route('manager.couriers.store') : route('manager.couriers.update', $courier) }}">
        @csrf
        @unless($isNew) @method('PUT') @endunless

        <x-ep.card>
            <div class="ep-grid" style="grid-template-columns:repeat(auto-fit,minmax(min(100%,220px),1fr));gap:1rem">
                <div class="ep-field">
                    <label class="ep-label" for="name">Nom complet</label>
                    <input class="ep-input" id="name" name="name" required maxlength="255"
                           value="{{ old('name', $courier->name) }}">
                    <x-input-error :messages="$errors->get('name')" class="ep-error" />
                </div>

                <div class="ep-field">
                    <label class="ep-label" for="email">E-mail</label>
                    <input class="ep-input" id="email" name="email" type="email" required maxlength="255"
                           value="{{ old('email', $courier->email) }}">
                    <x-input-error :messages="$errors->get('email')" class="ep-error" />
                </div>

                <div class="ep-field">
                    <label class="ep-label" for="phone">Téléphone</label>
                    <input class="ep-input ep-mono" id="phone" name="phone" maxlength="30"
                           value="{{ old('phone', $courier->phone) }}" placeholder="+225 07 00 00 00 00">
                    <p class="ep-hint">Transmis au client avec le nom du livreur.</p>
                </div>

                <div class="ep-field">
                    <label class="ep-label" for="zone">Quartier de rattachement</label>
                    <input class="ep-input" id="zone" name="zone" maxlength="80"
                           value="{{ old('zone', $courier->zone) }}" placeholder="Plateau">
                </div>

                <div class="ep-field">
                    <label class="ep-label" for="latitude">Latitude</label>
                    <input class="ep-input ep-mono" id="latitude" name="latitude" type="number" step="0.0000001"
                           value="{{ old('latitude', $courier->latitude) }}" placeholder="5.3280">
                    <p class="ep-hint">Sert à proposer le livreur le plus proche de la pharmacie.</p>
                </div>

                <div class="ep-field">
                    <label class="ep-label" for="longitude">Longitude</label>
                    <input class="ep-input ep-mono" id="longitude" name="longitude" type="number" step="0.0000001"
                           value="{{ old('longitude', $courier->longitude) }}" placeholder="-4.0210">
                </div>

                <div class="ep-field">
                    <label class="ep-label" for="password">
                        Mot de passe @unless($isNew)<span class="ep-hint">(laisser vide pour conserver)</span>@endunless
                    </label>
                    <input class="ep-input" id="password" name="password" type="password"
                           autocomplete="new-password" @required($isNew) minlength="8">
                    <x-input-error :messages="$errors->get('password')" class="ep-error" />
                </div>

                <div class="ep-field">
                    <label class="ep-label" for="password_confirmation">Confirmation</label>
                    <input class="ep-input" id="password_confirmation" name="password_confirmation" type="password"
                           autocomplete="new-password" @required($isNew) minlength="8">
                </div>
            </div>

            <label class="ep-choice" style="margin-top:1.25rem">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $courier->is_active ?? true))>
                Livreur actif — peut recevoir des courses
            </label>

            <x-slot:footer>
                <div class="ep-row">
                    <button type="submit" class="ep-btn ep-btn--primary">{{ $isNew ? 'Créer le livreur' : 'Enregistrer' }}</button>
                    <a href="{{ route('manager.couriers.index') }}" class="ep-btn ep-btn--ghost">Annuler</a>
                </div>
            </x-slot:footer>
        </x-ep.card>
    </form>
@endsection
