<div class="hidden md:flex md:flex-shrink-0">
    <div class="flex flex-col w-64 bg-white border-r border-gray-200">
        <div class="flex items-center justify-center h-16 px-4 bg-primary text-white">
            <h1 class="text-xl font-bold">E-PHARMA</h1>
        </div>

        <div class="flex flex-col flex-grow px-4 py-4 overflow-y-auto">
            <div class="flex items-center px-4 py-3 mb-2 text-sm font-medium text-gray-700 rounded-lg bg-gray-100">
                <i class="fas fa-user-shield mr-3"></i>
                Espace Pharmacie
            </div>

            <nav class="flex-1 space-y-2">
                <x-admin.sidebar-link
                    :href="url('/admin/dashboard')"
                    icon="fas fa-tachometer-alt"
                    label="Tableau de bord"
                    :active="request()->is('admin/dashboard')"
                />

                <x-admin.sidebar-link
                    :href="url('/admin/products')"
                    icon="fas fa-pills"
                    label="Gestion des produits"
                    :active="request()->is('admin/products*')"
                />

                <x-admin.sidebar-link
                    :href="url('/admin/stock')"
                    icon="fas fa-boxes"
                    label="Gestion du stock"
                    :active="request()->is('admin/stock*')"
                />

                <x-admin.sidebar-link
                    :href="url('/admin/orders')"
                    icon="fas fa-clipboard-list"
                    label="Commandes récentes"
                    :active="request()->is('admin/orders*')"
                />

                <x-admin.sidebar-link
                    :href="url('/admin/status')"
                    icon="fas fa-store"
                    label="Statut d'ouverture"
                    :active="request()->is('admin/status*')"
                />
            </nav>

            <div class="mt-auto">
                {{-- Si Breeze: route('logout') en POST --}}
                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full flex items-center px-4 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-100">
                            <i class="fas fa-sign-out-alt mr-3"></i>
                            Déconnexion
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}"
                       class="flex items-center px-4 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-100">
                        <i class="fas fa-sign-in-alt mr-3"></i>
                        Connexion
                    </a>
                @endauth
            </div>
        </div>
    </div>
</div>

{{-- Sidebar mobile (optionnel, même contenu) --}}
<div id="mobile-sidebar-overlay" class="hidden fixed inset-0 bg-black/40 z-40"></div>

<div id="mobile-sidebar" class="hidden fixed inset-y-0 left-0 w-72 bg-white border-r z-50 md:hidden">
    <div class="flex items-center justify-between h-16 px-4 bg-primary text-white">
        <h1 class="text-xl font-bold">E-PHARMA</h1>
        <button class="p-2" data-modal-close="mobile-sidebar">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="px-4 py-4 space-y-2">
        <x-admin.sidebar-link :href="url('/admin/dashboard')" icon="fas fa-tachometer-alt" label="Tableau de bord" :active="request()->is('admin/dashboard')" />
        <x-admin.sidebar-link :href="url('/admin/products')" icon="fas fa-pills" label="Gestion des produits" :active="request()->is('admin/products*')" />
        <x-admin.sidebar-link :href="url('/admin/stock')" icon="fas fa-boxes" label="Gestion du stock" :active="request()->is('admin/stock*')" />
        <x-admin.sidebar-link :href="url('/admin/orders')" icon="fas fa-clipboard-list" label="Commandes récentes" :active="request()->is('admin/orders*')" />
        <x-admin.sidebar-link :href="url('/admin/status')" icon="fas fa-store" label="Statut d'ouverture" :active="request()->is('admin/status*')" />
    </div>
</div>

<script>
    // close mobile sidebar from the X button
    document.addEventListener('DOMContentLoaded', () => {
        const closeBtns = document.querySelectorAll('[data-modal-close="mobile-sidebar"]');
        const sidebar = document.getElementById('mobile-sidebar');
        const overlay = document.getElementById('mobile-sidebar-overlay');

        closeBtns.forEach(btn => btn.addEventListener('click', () => {
            sidebar?.classList.add('hidden');
            overlay?.classList.add('hidden');
        }));
    });
</script>
