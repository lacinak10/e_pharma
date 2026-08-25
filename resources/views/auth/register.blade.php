<x-guest-layout>
    <h1 class="ep-h3" style="margin-bottom:.5rem">Créer un compte</h1>
    <p class="ep-small" style="margin-bottom:1.5rem">
        Pour commander et suivre vos livraisons à Abidjan.
    </p>

    <form method="POST" action="{{ route('register') }}" class="ep-stack" style="gap:1rem">
        @csrf

        <div class="ep-field">
            <label class="ep-label" for="name">Nom complet</label>
            <input class="ep-input" id="name" name="name" required autofocus autocomplete="name"
                   value="{{ old('name') }}">
            <x-input-error :messages="$errors->get('name')" class="ep-error" />
        </div>

        <div class="ep-field">
            <label class="ep-label" for="email">Adresse e-mail</label>
            <input class="ep-input" id="email" name="email" type="email" required autocomplete="username"
                   value="{{ old('email') }}">
            <x-input-error :messages="$errors->get('email')" class="ep-error" />
        </div>

        <div class="ep-field">
            <label class="ep-label" for="password">Mot de passe</label>
            <input class="ep-input" id="password" name="password" type="password" required
                   autocomplete="new-password" minlength="8">
            <p class="ep-hint">8 caractères minimum.</p>
            <x-input-error :messages="$errors->get('password')" class="ep-error" />
        </div>

        <div class="ep-field">
            <label class="ep-label" for="password_confirmation">Confirmer le mot de passe</label>
            <input class="ep-input" id="password_confirmation" name="password_confirmation" type="password"
                   required autocomplete="new-password" minlength="8">
            <x-input-error :messages="$errors->get('password_confirmation')" class="ep-error" />
        </div>

        <button type="submit" class="ep-btn ep-btn--primary ep-btn--block">Créer mon compte</button>

        <p class="ep-small" style="text-align:center;margin:0">
            Déjà inscrit ? <a href="{{ route('login') }}">Se connecter</a>
        </p>
    </form>
</x-guest-layout>
