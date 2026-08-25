<x-guest-layout>
    <h1 class="ep-h3" style="margin-bottom:.5rem">Vérifier votre adresse</h1>
    <p class="ep-small" style="margin-bottom:1.5rem">
        Merci de votre inscription. Confirmez votre adresse e-mail en cliquant sur le lien
        que nous venons de vous envoyer.
    </p>

    @if(session('status') === 'verification-link-sent')
        <p class="ep-flash ep-flash--success" style="margin-bottom:1rem">
            Un nouveau lien de vérification vient d'être envoyé.
        </p>
    @endif

    <div class="ep-row" style="justify-content:space-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="ep-btn ep-btn--primary">Renvoyer le lien</button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="ep-btn ep-btn--ghost">Se déconnecter</button>
        </form>
    </div>
</x-guest-layout>
