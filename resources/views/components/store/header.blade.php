@php
    $user      = auth()->user();
    $rating    = $storeRating ?? ['average' => 0, 'count' => 0];
    $inFlight  = $currentOrder ?? null;
    $cartCount = $cartCount ?? 0;
@endphp

{{-- Barre utilitaire : la promesse produit, avant tout le reste --}}
<div class="ep-utilitybar">
    <div class="ep-shell ep-utilitybar__inner">
        @if($rating['count'] > 0)
            <span class="ep-row ep-row--nowrap" style="gap:.375rem">
                <span class="ep-stars" aria-hidden="true">★★★★★</span>
                <strong class="ep-mono">{{ number_format($rating['average'], 1, ',', ' ') }}</strong>
                · {{ $rating['count'] }} avis de clients livrés
            </span>
            <span style="width:1px;height:14px;background:#DAD6CA"></span>
        @endif
        <span class="ep-row ep-row--nowrap" style="gap:.375rem;color:#8A5A10;font-weight:600">
            <span style="width:6px;height:6px;border-radius:50%;background:var(--ep-amber)"></span>
            Disponibilité vérifiée en moins de 5 minutes
        </span>
        <a href="tel:+2252722000000" class="ep-spacer ep-mono">+225 27 22 00 00 00</a>
    </div>
</div>

<header class="ep-header">
    <div class="ep-shell ep-header__inner">
        <a href="{{ route('store.home') }}" class="ep-logo">
            <span class="ep-logo__mark" aria-hidden="true">e</span>
            <span class="ep-logo__word">ePharma</span>
        </a>

        <form method="GET" action="{{ route('store.medicines.index') }}" class="ep-header__search" role="search">
            <label for="q" class="ep-sr-only">Rechercher un médicament</label>
            <div class="ep-search">
                <input type="search" id="q" name="q" value="{{ request('q') }}"
                       placeholder="Rechercher un médicament, une indication, un dosage…">
                <button type="submit">Rechercher</button>
            </div>
        </form>

        <div class="ep-account ep-header__account">
            @auth
                <a href="{{ $user->isClient() ? route('profile.edit') : route('admin.dashboard') }}"
                   class="ep-account__link ep-account__link--hide">
                    <span>Bonjour {{ explode(' ', $user->name)[0] }}</span>
                    <strong>Mon compte</strong>
                </a>
                <a href="{{ route('store.orders.index') }}" class="ep-account__link ep-account__link--hide">
                    <span>{{ $inFlight ? '1 en cours' : 'Suivi' }}</span>
                    <strong>Mes commandes</strong>
                </a>
            @else
                <a href="{{ route('login') }}" class="ep-account__link ep-account__link--hide">
                    <span>Bonjour</span>
                    <strong>Se connecter</strong>
                </a>
            @endauth

            <a href="{{ route('store.cart.index') }}" class="ep-cart-pill">
                Panier
                <span class="ep-cart-pill__count">{{ $cartCount }}</span>
            </a>
        </div>
    </div>

    <nav class="ep-mainnav" aria-label="Navigation du catalogue">
        <div class="ep-shell ep-mainnav__inner">
            <a href="{{ route('store.medicines.index') }}" class="ep-mainnav__link"
               @if(request()->routeIs('store.medicines.*')) aria-current="page" @endif>Médicaments</a>
            <a href="{{ route('store.how') }}" class="ep-mainnav__link"
               @if(request()->routeIs('store.how')) aria-current="page" @endif>Comment ça marche</a>
            <a href="{{ route('store.partners') }}" class="ep-mainnav__link"
               @if(request()->routeIs('store.partners')) aria-current="page" @endif>Nos partenaires</a>
            <a href="{{ route('store.reviews') }}" class="ep-mainnav__link"
               @if(request()->routeIs('store.reviews')) aria-current="page" @endif>Avis clients</a>

            <a href="{{ route('store.prescriptions.create') }}" class="ep-btn ep-btn--primary ep-btn--md ep-mainnav__cta">
                <span aria-hidden="true">↑</span> Téléverser une ordonnance
            </a>
        </div>
    </nav>
</header>
