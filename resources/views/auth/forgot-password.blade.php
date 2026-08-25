<x-guest-layout>
    <h1 class="ep-h3" style="margin-bottom:.5rem">Mot de passe oublié</h1>
    <p class="ep-small" style="margin-bottom:1.5rem">
        Indiquez votre adresse e-mail : nous vous envoyons un lien pour en choisir un nouveau.
    </p>

    <x-auth-session-status :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="ep-stack" style="gap:1rem">
        @csrf

        <div class="ep-field">
            <label class="ep-label" for="email">Adresse e-mail</label>
            <input class="ep-input" id="email" name="email" type="email" required autofocus
                   value="{{ old('email') }}">
            <x-input-error :messages="$errors->get('email')" class="ep-error" />
        </div>

        <button type="submit" class="ep-btn ep-btn--primary ep-btn--block">Envoyer le lien</button>

        <p class="ep-small" style="text-align:center;margin:0">
            <a href="{{ route('login') }}">Retour à la connexion</a>
        </p>
    </form>
</x-guest-layout>
