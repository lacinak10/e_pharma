@php
    $cart = session('cart', []);
    $cartCount = collect($cart)->sum('qty');
@endphp

<header class="bg-white/90 backdrop-blur sticky top-0 z-50 border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-3">
        <a href="{{ route('store.home') }}" class="flex items-center gap-2">
            <i class="fa-solid fa-pills text-blue-600 text-2xl"></i>
            <span class="text-xl font-extrabold text-blue-700 tracking-tight">E-PHARMA</span>
        </a>

        <nav class="hidden md:flex items-center gap-6 text-sm font-medium">
            <a href="{{ route('store.home') }}" class="{{ request()->routeIs('store.home') ? 'text-blue-600' : 'text-gray-600 hover:text-blue-600' }}">Accueil</a>
            <a href="{{ route('store.medicines.index') }}" class="{{ request()->routeIs('store.medicines.*') ? 'text-blue-600' : 'text-gray-600 hover:text-blue-600' }}">Médicaments</a>
            <a href="{{ route('store.prescriptions.create') }}" class="{{ request()->routeIs('store.prescriptions.*') ? 'text-blue-600' : 'text-gray-600 hover:text-blue-600' }}">Télécharger ordonnance</a>
            @auth
                <a href="{{ route('store.orders.index') }}" class="{{ request()->routeIs('store.orders.*') ? 'text-blue-600' : 'text-gray-600 hover:text-blue-600' }}">Mes commandes</a>
            @endauth
        </nav>

        <div class="flex items-center gap-3">
            <a href="{{ route('store.cart.index') }}" class="relative p-2 rounded-xl hover:bg-gray-100" aria-label="Panier">
                <i class="fa-solid fa-cart-shopping text-gray-700 text-lg"></i>
                @if($cartCount > 0)
                    <span class="absolute -top-1 -right-1 bg-blue-600 text-white text-[11px] font-bold rounded-full h-5 min-w-5 px-1 flex items-center justify-center">
                        {{ $cartCount > 99 ? '99+' : $cartCount }}
                    </span>
                @endif
            </a>

            @guest
                <a href="{{ route('login') }}" class="hidden sm:inline-flex px-4 py-2 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">
                    Connexion
                </a>
            @endguest

            @auth
                <div x-data="{open:false}" class="relative">
                    <button @click="open=!open" class="flex items-center gap-2 p-2 rounded-xl hover:bg-gray-100">
                        <span class="hidden sm:block text-sm font-semibold">{{ auth()->user()->name }}</span>
                        <i class="fa-solid fa-chevron-down text-xs text-gray-500"></i>
                    </button>

                    <div x-show="open" x-transition @click.outside="open=false"
                         class="absolute right-0 mt-2 w-56 bg-white border border-gray-200 rounded-2xl shadow-lg overflow-hidden z-50"
                         style="display:none;">
                        <a href="{{ route('store.orders.index') }}" class="block px-4 py-3 text-sm hover:bg-gray-50">
                            <i class="fa-regular fa-clipboard mr-2"></i> Mes commandes
                        </a>
                        <div class="border-t border-gray-100"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="w-full text-left px-4 py-3 text-sm text-red-600 hover:bg-red-50">
                                <i class="fa-solid fa-right-from-bracket mr-2"></i> Déconnexion
                            </button>
                        </form>
                    </div>
                </div>
            @endauth

            <button class="md:hidden p-2 rounded-xl hover:bg-gray-100" id="mobile-menu-btn" type="button" aria-label="Menu">
                <i class="fa-solid fa-bars text-gray-700"></i>
            </button>
        </div>
    </div>

    <div id="mobile-menu" class="hidden md:hidden border-t bg-white">
        <div class="max-w-7xl mx-auto px-4 py-3 flex flex-col gap-2 text-sm">
            <a href="{{ route('store.home') }}" class="py-2">Accueil</a>
            <a href="{{ route('store.medicines.index') }}" class="py-2">Médicaments</a>
            <a href="{{ route('store.prescriptions.create') }}" class="py-2">Télécharger ordonnance</a>
            @auth
                <a href="{{ route('store.orders.index') }}" class="py-2">Mes commandes</a>
            @endauth
            @guest
                <a href="{{ route('login') }}" class="py-2 text-blue-600 font-semibold">Connexion</a>
            @endguest
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.getElementById('mobile-menu-btn');
            const menu = document.getElementById('mobile-menu');
            btn?.addEventListener('click', () => menu?.classList.toggle('hidden'));
        });
    </script>
</header>
