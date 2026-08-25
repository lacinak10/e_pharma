<x-guest-layout>
    <h1 class="ep-h3" style="margin-bottom:.5rem">Confirmer votre mot de passe</h1>
    <p class="ep-small" style="margin-bottom:1.5rem">
        Cette zone est sensible : merci de confirmer votre mot de passe avant de continuer.
    </p>

    <form method="POST" action="{{ route('password.confirm') }}" class="ep-stack" style="gap:1rem">
        @csrf

        <div class="ep-field">
            <label class="ep-label" for="password">Mot de passe</label>
            <input class="ep-input" id="password" name="password" type="password" required autofocus
                   autocomplete="current-password">
            <x-input-error :messages="$errors->get('password')" class="ep-error" />
        </div>

        <button type="submit" class="ep-btn ep-btn--primary ep-btn--block">Confirmer</button>
    </form>
</x-guest-layout>
