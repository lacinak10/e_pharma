<x-guest-layout>
    <h1 class="ep-h3" style="margin-bottom:.5rem">Nouveau mot de passe</h1>
    <p class="ep-small" style="margin-bottom:1.5rem">Choisissez un mot de passe pour votre compte.</p>

    <form method="POST" action="{{ route('password.store') }}" class="ep-stack" style="gap:1rem">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="ep-field">
            <label class="ep-label" for="email">Adresse e-mail</label>
            <input class="ep-input" id="email" name="email" type="email" required autofocus
                   value="{{ old('email', $request->email) }}">
            <x-input-error :messages="$errors->get('email')" class="ep-error" />
        </div>

        <div class="ep-field">
            <label class="ep-label" for="password">Nouveau mot de passe</label>
            <input class="ep-input" id="password" name="password" type="password" required
                   autocomplete="new-password" minlength="8">
            <x-input-error :messages="$errors->get('password')" class="ep-error" />
        </div>

        <div class="ep-field">
            <label class="ep-label" for="password_confirmation">Confirmer</label>
            <input class="ep-input" id="password_confirmation" name="password_confirmation" type="password"
                   required autocomplete="new-password" minlength="8">
            <x-input-error :messages="$errors->get('password_confirmation')" class="ep-error" />
        </div>

        <button type="submit" class="ep-btn ep-btn--primary ep-btn--block">Enregistrer</button>
    </form>
</x-guest-layout>
