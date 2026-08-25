<x-guest-layout>
    <h1 class="ep-h3" style="margin-bottom:.5rem">Se connecter</h1>
    <p class="ep-small" style="margin-bottom:1.5rem">
        Retrouvez vos commandes et suivez vos livraisons en direct.
    </p>

    <x-auth-session-status :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="ep-stack" style="gap:1rem">
        @csrf

        <div class="ep-field">
            <label class="ep-label" for="email">Adresse e-mail</label>
            <input class="ep-input" id="email" name="email" type="email" required autofocus
                   autocomplete="username" value="{{ old('email') }}">
            <x-input-error :messages="$errors->get('email')" class="ep-error" />
        </div>

        <div class="ep-field">
            <label class="ep-label" for="password">Mot de passe</label>
            <input class="ep-input" id="password" name="password" type="password" required
                   autocomplete="current-password">
            <x-input-error :messages="$errors->get('password')" class="ep-error" />
        </div>

        <label class="ep-choice">
            <input type="checkbox" id="remember_me" name="remember">
            Rester connecté sur cet appareil
        </label>

        <button type="submit" class="ep-btn ep-btn--primary ep-btn--block">Se connecter</button>

        <div class="ep-row" style="justify-content:space-between">
            <a href="{{ route('register') }}" class="ep-small">Créer un compte</a>
            @if(Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="ep-small">Mot de passe oublié ?</a>
            @endif
        </div>
    </form>
</x-guest-layout>
