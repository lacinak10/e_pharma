@extends('layouts.store')

@section('title', 'Mon compte — ePharma')

@section('content')
<div class="ep-shell ep-section--tight">
    <h1 class="ep-h2" style="margin-bottom:1.5rem">Mon compte</h1>

    @if(session('status') === 'profile-updated')
        <p class="ep-flash ep-flash--success" style="margin-bottom:1.25rem">Vos informations ont été mises à jour.</p>
    @endif
    @if(session('status') === 'password-updated')
        <p class="ep-flash ep-flash--success" style="margin-bottom:1.25rem">Votre mot de passe a été modifié.</p>
    @endif

    <div class="ep-split">
        <div class="ep-stack">

            {{-- Informations personnelles --}}
            <x-ep.card title="Mes informations">
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf @method('PATCH')

                    <div class="ep-grid" style="grid-template-columns:repeat(auto-fit,minmax(min(100%,220px),1fr));gap:1rem">
                        <div class="ep-field">
                            <label class="ep-label" for="name">Nom complet</label>
                            <input class="ep-input" id="name" name="name" required maxlength="255"
                                   value="{{ old('name', $user->name) }}" autocomplete="name">
                            <x-input-error :messages="$errors->get('name')" class="ep-error" />
                        </div>

                        <div class="ep-field">
                            <label class="ep-label" for="email">Adresse e-mail</label>
                            <input class="ep-input" id="email" name="email" type="email" required maxlength="255"
                                   value="{{ old('email', $user->email) }}" autocomplete="username">
                            <x-input-error :messages="$errors->get('email')" class="ep-error" />
                        </div>
                    </div>

                    <button type="submit" class="ep-btn ep-btn--primary" style="margin-top:1.25rem">Enregistrer</button>
                </form>
            </x-ep.card>

            {{-- Mot de passe --}}
            <x-ep.card title="Mot de passe">
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf @method('PUT')

                    <div class="ep-grid" style="grid-template-columns:repeat(auto-fit,minmax(min(100%,200px),1fr));gap:1rem">
                        <div class="ep-field">
                            <label class="ep-label" for="current_password">Mot de passe actuel</label>
                            <input class="ep-input" id="current_password" name="current_password" type="password"
                                   autocomplete="current-password">
                            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="ep-error" />
                        </div>

                        <div class="ep-field">
                            <label class="ep-label" for="update_password">Nouveau</label>
                            <input class="ep-input" id="update_password" name="password" type="password"
                                   autocomplete="new-password" minlength="8">
                            <x-input-error :messages="$errors->updatePassword->get('password')" class="ep-error" />
                        </div>

                        <div class="ep-field">
                            <label class="ep-label" for="update_password_confirmation">Confirmer</label>
                            <input class="ep-input" id="update_password_confirmation" name="password_confirmation"
                                   type="password" autocomplete="new-password" minlength="8">
                            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="ep-error" />
                        </div>
                    </div>

                    <button type="submit" class="ep-btn ep-btn--primary" style="margin-top:1.25rem">Changer le mot de passe</button>
                </form>
            </x-ep.card>

            {{-- Suppression --}}
            <x-ep.card title="Supprimer mon compte">
                <p class="ep-small" style="margin:0 0 1rem;max-width:60ch">
                    La suppression est définitive. Vos commandes passées et les avis que vous avez laissés
                    sur vos livreurs seront supprimés avec votre compte.
                </p>

                <form method="POST" action="{{ route('profile.destroy') }}">
                    @csrf @method('DELETE')

                    <div class="ep-field" style="max-width:320px">
                        <label class="ep-label" for="delete_password">Confirmez avec votre mot de passe</label>
                        <input class="ep-input" id="delete_password" name="password" type="password" required
                               autocomplete="current-password">
                        <x-input-error :messages="$errors->userDeletion->get('password')" class="ep-error" />
                    </div>

                    <button type="submit" class="ep-btn ep-btn--danger" style="margin-top:1rem"
                            onclick="return confirm('Supprimer définitivement votre compte ?')">
                        Supprimer mon compte
                    </button>
                </form>
            </x-ep.card>
        </div>

        <div class="ep-stack">
            <x-ep.card title="Mon profil">
                <div class="ep-row ep-row--nowrap" style="gap:.75rem">
                    <img src="{{ $user->avatar_url }}" alt=""
                         style="width:56px;height:56px;border-radius:50%;object-fit:cover;flex:none">
                    <div style="min-width:0">
                        <p style="font-size:.9375rem;font-weight:650;margin:0">{{ $user->name }}</p>
                        <p class="ep-small" style="margin:0">
                            Client depuis le {{ $user->created_at->locale('fr')->isoFormat('D MMMM YYYY') }}
                        </p>
                    </div>
                </div>

                @if($user->phone)
                    <p class="ep-mono ep-small" style="margin:1rem 0 0">{{ $user->phone }}</p>
                @endif
                @if($user->zone)
                    <p class="ep-small" style="margin:.25rem 0 0">{{ $user->zone }}</p>
                @endif
            </x-ep.card>

            <x-ep.card title="Mes commandes">
                <p class="ep-small" style="margin:0 0 1rem">
                    Retrouvez le suivi de vos livraisons et notez vos livreurs.
                </p>
                <a href="{{ route('store.orders.index') }}" class="ep-btn ep-btn--ghost ep-btn--block">
                    Voir mes commandes
                </a>
            </x-ep.card>
        </div>
    </div>
</div>
@endsection
