{{-- Barre d'onglets mobile : le parcours client tient dans le pouce --}}
<nav class="ep-tabbar" aria-label="Navigation mobile">
    <a href="{{ route('store.home') }}" class="ep-tabbar__link"
       @if(request()->routeIs('store.home')) aria-current="page" @endif>
        <i class="fa-solid fa-house" aria-hidden="true"></i> Accueil
    </a>
    <a href="{{ route('store.medicines.index') }}" class="ep-tabbar__link"
       @if(request()->routeIs('store.medicines.*')) aria-current="page" @endif>
        <i class="fa-solid fa-pills" aria-hidden="true"></i> Médicaments
    </a>
    <a href="{{ route('store.prescriptions.create') }}" class="ep-tabbar__link"
       @if(request()->routeIs('store.prescriptions.*')) aria-current="page" @endif>
        <i class="fa-solid fa-file-medical" aria-hidden="true"></i> Ordonnance
    </a>
    <a href="{{ route('store.cart.index') }}" class="ep-tabbar__link"
       @if(request()->routeIs('store.cart.*')) aria-current="page" @endif>
        <i class="fa-solid fa-basket-shopping" aria-hidden="true"></i>
        Panier{{ ($cartCount ?? 0) > 0 ? ' (' . $cartCount . ')' : '' }}
    </a>
    <a href="{{ auth()->check() ? route('store.orders.index') : route('login') }}" class="ep-tabbar__link"
       @if(request()->routeIs('store.orders.*')) aria-current="page" @endif>
        <i class="fa-solid fa-truck-fast" aria-hidden="true"></i> Suivi
    </a>
</nav>
