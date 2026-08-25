@extends('layouts.admin')

@section('title', 'Créer un utilisateur — ePharma')
@section('page_title', 'Créer un utilisateur')
@section('page_subtitle', 'Manager, livreur ou client')

@section('content')
    <form method="POST" action="{{ route('manager.users.store') }}" style="max-width:760px">
        @csrf

        <x-ep.card>
            <fieldset style="border:0;padding:0;margin:0 0 1.25rem">
                <legend class="ep-label" style="margin-bottom:.5rem">Rôle</legend>
                <div class="ep-grid" style="grid-template-columns:repeat(auto-fit,minmax(min(100%,180px),1fr));gap:.5rem">
                    @foreach([
                        'client'  => ['Client', 'Commande et suit ses livraisons'],
                        'courier' => ['Livreur', 'Prend en charge les courses'],
                        'manager' => ['Manager', 'Valide et vérifie la disponibilité'],
                    ] as $value => [$label, $help])
                        <label class="ep-choice">
                            <input type="radio" name="role" value="{{ $value }}" @checked(old('role', 'client') === $value)>
                            <span>
                                <strong>{{ $label }}</strong><br>
                                <span class="ep-small">{{ $help }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('role')" class="ep-error" />
            </fieldset>

            <div class="ep-grid" style="grid-template-columns:repeat(auto-fit,minmax(min(100%,220px),1fr));gap:1rem">
                <div class="ep-field">
                    <label class="ep-label" for="name">Nom</label>
                    <input class="ep-input" id="name" name="name" required maxlength="255" value="{{ old('name') }}">
                    <x-input-error :messages="$errors->get('name')" class="ep-error" />
                </div>

                <div class="ep-field">
                    <label class="ep-label" for="prenom">Prénom <span class="ep-hint">(facultatif)</span></label>
                    <input class="ep-input" id="prenom" name="prenom" maxlength="255" value="{{ old('prenom') }}">
                </div>

                <div class="ep-field">
                    <label class="ep-label" for="email">E-mail</label>
                    <input class="ep-input" id="email" name="email" type="email" required maxlength="255" value="{{ old('email') }}">
                    <x-input-error :messages="$errors->get('email')" class="ep-error" />
                </div>

                <div class="ep-field">
                    <label class="ep-label" for="phone">Téléphone</label>
                    <input class="ep-input ep-mono" id="phone" name="phone" maxlength="30" value="{{ old('phone') }}"
                           placeholder="+225 07 00 00 00 00">
                </div>

                <div class="ep-field">
                    <label class="ep-label" for="password">Mot de passe</label>
                    <input class="ep-input" id="password" name="password" type="password" required minlength="8"
                           autocomplete="new-password">
                    <p class="ep-hint">8 caractères minimum.</p>
                    <x-input-error :messages="$errors->get('password')" class="ep-error" />
                </div>

                <div class="ep-field">
                    <label class="ep-label" for="password_confirmation">Confirmation</label>
                    <input class="ep-input" id="password_confirmation" name="password_confirmation" type="password"
                           required minlength="8" autocomplete="new-password">
                </div>
            </div>

            <label class="ep-choice" style="margin-top:1.25rem">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
                Compte actif dès la création
            </label>

            <x-slot:footer>
                <button type="submit" class="ep-btn ep-btn--primary">Créer l'utilisateur</button>
            </x-slot:footer>
        </x-ep.card>
    </form>
@endsection
