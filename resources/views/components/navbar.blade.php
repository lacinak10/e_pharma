<!-- Header/Navigation -->
<header class="bg-white shadow-sm sticky top-0 z-50">
    <div class="container mx-auto px-4 py-3 flex justify-between items-center">
        <a href="{{ url('/') }}">
            <div class="flex items-center space-x-2">
                <i class="fas fa-pills text-blue-500 text-2xl"></i>
                <h1 class="text-xl font-bold text-blue-600">E-PHARMA</h1>
            </div>
        </a>

        <nav class="hidden md:flex space-x-6">
            <a href="{{ url('/') }}"
               class="nav-link hover:text-blue-500 transition {{ request()->is('/') ? 'text-blue-500' : 'text-gray-500' }}">
                Accueil
            </a>

            <a href="{{ url('/medicaments') }}"
               class="nav-link hover:text-blue-500 transition {{ request()->is('medicaments*') ? 'text-blue-500' : 'text-gray-500' }}">
                Médicaments
            </a>

            <a href="{{ url('/scan-ordonnance') }}"
               class="nav-link hover:text-blue-500 transition {{ request()->is('scan-ordonnance*') ? 'text-blue-500' : 'text-gray-500' }}">
                Télécharger votre ordonnance
            </a>
        </nav>

        <div class="flex items-center space-x-4">
            <a id="cart" href="{{ url('/cart') }}" class="relative cursor-pointer">
                <i class="fas fa-shopping-cart text-gray-600 text-xl"></i>

                {{-- Exemple: badge panier (remplace par ta variable réelle) --}}
                <span
                    class="absolute -top-2 -right-2 bg-blue-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                    {{ session('cart') ? count(session('cart')) : 0 }}
                </span>
            </a>

            @guest
                <a href="{{ route('login') }}"
                   class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md transition">
                    Connexion
                </a>
            @endguest

            @auth
                <a href="{{ route('manager.medicines.index') ?? url('/dashboard') }}"
                   class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md transition">
                    Dashboard
                </a>
            @endauth

            <button id="mobile-menu-button" class="md:hidden text-gray-600" type="button">
                <i class="fas fa-bars text-xl"></i>
            </button>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div id="mobile-menu" class="hidden md:hidden bg-white border-t">
        <div class="container mx-auto px-4 py-2 flex flex-col space-y-3">
            <a href="{{ url('/') }}"
               class="py-2 hover:text-blue-500 transition {{ request()->is('/') ? 'text-blue-500' : 'text-gray-600' }}">
                Accueil
            </a>

            <a href="{{ url('/medicaments') }}"
               class="py-2 hover:text-blue-500 transition {{ request()->is('medicaments*') ? 'text-blue-500' : 'text-gray-600' }}">
                Médicaments
            </a>

            <a href="{{ url('/scan-ordonnance') }}"
               class="py-2 hover:text-blue-500 transition {{ request()->is('scan-ordonnance*') ? 'text-blue-500' : 'text-gray-600' }}">
                Télécharger votre ordonnance
            </a>

            @guest
                <a href="{{ route('login') }}" class="py-2 text-blue-500">Connexion</a>
            @endguest

            @auth
                <a href="{{ route('manager.medicines.index') ?? url('/dashboard') }}" class="py-2 text-blue-500">Dashboard</a>
            @endauth
        </div>
    </div>
</header>
