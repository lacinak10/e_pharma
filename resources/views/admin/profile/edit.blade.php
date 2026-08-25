@extends('layouts.admin')

@section('title', 'Mon profil — ePharma')
@section('page_title', 'Mon profil')

@section('content')
    <div class="ep-split">
        <form method="POST" action="{{ route('admin.profile.update') }}">
            @csrf @method('PUT')

            <x-ep.card title="Mes informations">
                <div class="ep-grid" style="grid-template-columns:repeat(auto-fit,minmax(min(100%,220px),1fr));gap:1rem">
                    <div class="ep-field">
                        <label class="ep-label" for="name">Nom</label>
                        <input class="ep-input" id="name" name="name" required maxlength="255"
                               value="{{ old('name', $user->name) }}">
                        <x-input-error :messages="$errors->get('name')" class="ep-error" />
                    </div>

                    <div class="ep-field">
                        <label class="ep-label" for="email">E-mail</label>
                        <input class="ep-input" id="email" name="email" type="email" required maxlength="255"
                               value="{{ old('email', $user->email) }}">
                        <x-input-error :messages="$errors->get('email')" class="ep-error" />
                    </div>

                    <div class="ep-field">
                        <label class="ep-label" for="password">
                            Nouveau mot de passe <span class="ep-hint">(laisser vide pour conserver)</span>
                        </label>
                        <input class="ep-input" id="password" name="password" type="password"
                               minlength="8" autocomplete="new-password">
                        <x-input-error :messages="$errors->get('password')" class="ep-error" />
                    </div>
                </div>

                <x-slot:footer>
                    <button type="submit" class="ep-btn ep-btn--primary">Enregistrer</button>
                </x-slot:footer>
            </x-ep.card>
        </form>

        <x-ep.card title="Mon compte">
            <div class="ep-row ep-row--nowrap" style="gap:.75rem">
                <img src="{{ $user->avatar_url }}" alt=""
                     style="width:56px;height:56px;border-radius:50%;object-fit:cover;flex:none">
                <div style="min-width:0">
                    <p style="font-size:.9375rem;font-weight:650;margin:0">{{ $user->name }}</p>
                    <p class="ep-small" style="margin:0">
                        {{ $user->isManager() ? 'Manager' : ($user->isCourier() ? 'Livreur' : 'Utilisateur') }}
                        @if($user->zone) · {{ $user->zone }} @endif
                    </p>
                </div>
            </div>

            <p class="ep-hint" style="margin-top:1rem">
                Votre portrait est attribué automatiquement à partir de la banque d'images
                libres de droit du projet.
            </p>
        </x-ep.card>
    </div>
@endsection
