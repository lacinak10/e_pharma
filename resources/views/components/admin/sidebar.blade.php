@php
    $role = auth()->user()->role ?? 'guest';
    $isManager = $role === 'manager';
    $isCourier = $role === 'courier';

    $roleLabel = $isManager ? 'Espace Manager' : ($isCourier ? 'Espace Livreur' : 'Espace Admin');
    $roleIcon  = $isManager ? 'fa-solid fa-user-shield' : ($isCourier ? 'fa-solid fa-motorcycle' : 'fa-solid fa-user');

    $unreadCount = auth()->user()->unreadNotifications->count();

    // Menu commun (manager + livreur)
    $menu = [
        [
            'title' => 'Général',
            'items' => [
                [
                    'href'   => url('/admin/dashboard'),
                    'icon'   => 'fa-solid fa-gauge-high',
                    'label'  => 'Tableau de bord',
                    'active' => request()->is('admin/dashboard'),
                ],
                [
                    'href'   => url('/admin/notifications'),
                    'icon'   => 'fa-regular fa-bell',
                    'label'  => 'Notifications',
                    'active' => request()->is('admin/notifications*'),
                    'badge'  => $unreadCount > 0 ? $unreadCount : null,
                ],
            ],
        ],
    ];

    // Bloc Manager
    if ($isManager) {
        $menu[] = [
            'title' => 'Catalogue',
            'items' => [
                [
                    'href'   => url('/admin/medicines'),
                    'icon'   => 'fa-solid fa-pills',
                    'label'  => 'Médicaments',
                    'active' => request()->is('admin/medicines*'),
                ],
                [
                    'href'   => url('/admin/stock'),
                    'icon'   => 'fa-solid fa-boxes-stacked',
                    'label'  => 'Stock',
                    'active' => request()->is('admin/stock*'),
                ],
                [
                    'href'   => url('/admin/categories'),
                    'icon'   => 'fa-solid fa-tags',
                    'label'  => 'Catégories',
                    'active' => request()->is('admin/categories*'),
                ]
            ],
        ];

        $menu[] = [
            'title' => 'Commandes & Livraisons',
            'items' => [
                [
                    'href'   => url('/admin/orders'),
                    'icon'   => 'fa-solid fa-clipboard-list',
                    'label'  => 'Commandes',
                    'active' => request()->is('admin/orders*') && !request('status'),
                ],
                [
                    'href'   => url('/admin/assignments'),
                    'icon'   => 'fa-solid fa-user-check',
                    'label'  => 'Affectations',
                    'active' => request()->is('admin/assignments*'),
                ],
                [
                    'href'   => url('/admin/deliveries'),
                    'icon'   => 'fa-solid fa-truck-fast',
                    'label'  => 'Suivi livraisons',
                    'active' => request()->is('admin/deliveries*'),
                ],

                // Raccourcis filtres
                [
                    'href'   => url('/admin/orders?status=pending_courier'),
                    'icon'   => 'fa-solid fa-hourglass-half',
                    'label'  => 'En attente livreur',
                    'active' => request()->is('admin/orders') && request('status') === 'pending_courier',
                ],
                [
                    'href'   => url('/admin/orders?status=assigned'),
                    'icon'   => 'fa-solid fa-share-from-square',
                    'label'  => 'Affectées',
                    'active' => request()->is('admin/orders') && request('status') === 'assigned',
                ],
                [
                    'href'   => url('/admin/orders?status=IN_DELIVERY'),
                    'icon'   => 'fa-solid fa-truck',
                    'label'  => 'En livraison',
                    'active' => request()->is('admin/orders') && request('status') === 'IN_DELIVERY',
                ],
                [
                    'href'   => url('/admin/orders?status=DELIVERED'),
                    'icon'   => 'fa-solid fa-circle-check',
                    'label'  => 'Livrées',
                    'active' => request()->is('admin/orders') && request('status') === 'DELIVERED',
                ],
            ],
        ];

        $menu[] = [
            'title' => 'Utilisateurs',
            'items' => [
                [
                    'href'   => url('/admin/users/create'),
                    'icon'   => 'fa-solid fa-user-plus',
                    'label'  => 'Créer un utilisateur',
                    'active' => request()->is('admin/users/create'),
                ],
                [
                    'href'   => url('/admin/customers'),
                    'icon'   => 'fa-solid fa-users',
                    'label'  => 'Clients',
                    'active' => request()->is('admin/customers*'),
                ],
                [
                    'href'   => url('/admin/couriers'),
                    'icon'   => 'fa-solid fa-motorcycle',
                    'label'  => 'Livreurs',
                    'active' => request()->is('admin/couriers*'),
                ],
            ],
        ];
    }

    // Bloc Livreur
    if ($isCourier) {
        $menu[] = [
            'title' => 'Mes livraisons',
            'items' => [
                [
                    'href'   => url('/admin/my-orders'),
                    'icon'   => 'fa-solid fa-inbox',
                    'label'  => 'Assignées à moi',
                    'active' => request()->is('admin/my-orders') && !request('status'),
                ],
                [
                    'href'   => url('/admin/my-orders?status=IN_DELIVERY'),
                    'icon'   => 'fa-solid fa-truck',
                    'label'  => 'En cours',
                    'active' => request()->is('admin/my-orders') && request('status') === 'IN_DELIVERY',
                ],
                [
                    'href'   => url('/admin/my-orders?status=DELIVERED'),
                    'icon'   => 'fa-solid fa-circle-check',
                    'label'  => 'Historique',
                    'active' => request()->is('admin/my-orders') && request('status') === 'DELIVERED',
                ],
            ],
        ];
    }

    // Paramètres (commun)
    $menu[] = [
        'title' => 'Compte',
        'items' => [
            [
                'href'   => url('/admin/profile'),
                'icon'   => 'fa-solid fa-user-circle',
                'label'  => 'Mon profil',
                'active' => request()->is('admin/profile*'),
            ],
            [
                'href'   => url('/admin/settings'),
                'icon'   => 'fa-solid fa-gear',
                'label'  => 'Paramètres',
                'active' => request()->is('admin/settings*'),
            ],
        ],
    ];
@endphp

{{-- Desktop --}}
<div class="hidden md:flex  md:flex-shrink-0">
    <div class="flex flex-col items-center justify-center w-72 bg-white border-r border-gray-200">
       <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl overflow-hidden shadow-sm border-2 border-gray-200 bg-white flex items-center justify-center transition-transform duration-300 group-hover:scale-105">
            <img src="{{ asset('/assets/images/logo.jpeg') }}" 
             alt="logo" 
             class="w-full h-full object-cover">
         </div>

        <div class="flex flex-col flex-grow px-4 py-4 overflow-y-auto">
            <div class="flex items-center justify-between px-4 py-3 mb-3 text-sm font-medium text-gray-700 rounded-lg bg-gray-100">
                <div class="flex items-center">
                    <i class="{{ $roleIcon }} mr-3 text-gray-600"></i>
                    <span>{{ $roleLabel }}</span>
                </div>
                @auth
                    <span class="text-xs px-2 py-0.5 rounded-full bg-white text-gray-700 border border-gray-200">
                        {{ auth()->user()->name }}
                    </span>
                @endauth
            </div>

            <x-admin.partials.sidebar-nav :menu="$menu" />

            <div class="mt-auto pt-4 border-t border-gray-100">
                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full flex items-center px-4 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-100">
                            <i class="fa-solid fa-right-from-bracket mr-3"></i>
                            Déconnexion
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}"
                       class="flex items-center px-4 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-100">
                        <i class="fa-solid fa-right-to-bracket mr-3"></i>
                        Connexion
                    </a>
                @endauth
            </div>
        </div>
    </div>
</div>

{{-- Mobile --}}
<div id="mobile-sidebar-overlay" class="hidden fixed inset-0 bg-black/40 z-40"></div>

<div id="mobile-sidebar" class="hidden fixed inset-y-0 left-0 w-80 bg-white border-r z-50 md:hidden">
    <div class="flex items-center justify-between h-16 px-4 bg-primary text-white">
        <h1 class="text-xl font-bold">E-PHARMA</h1>
        <button class="p-2" data-modal-close="mobile-sidebar" aria-label="Fermer">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <div class="px-4 py-4 overflow-y-auto h-[calc(100vh-4rem)]">
        <div class="flex items-center px-4 py-3 mb-3 text-sm font-medium text-gray-700 rounded-lg bg-gray-100">
            <i class="{{ $roleIcon }} mr-3 text-gray-600"></i>
            {{ $roleLabel }}
        </div>

        <x-admin.partials.sidebar-nav :menu="$menu" />

        <div class="mt-6 pt-4 border-t border-gray-100">
            @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center px-4 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-100">
                        <i class="fa-solid fa-right-from-bracket mr-3"></i>
                        Déconnexion
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}"
                   class="flex items-center px-4 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-100">
                    <i class="fa-solid fa-right-to-bracket mr-3"></i>
                    Connexion
                </a>
            @endauth
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const sidebar = document.getElementById('mobile-sidebar');
        const overlay = document.getElementById('mobile-sidebar-overlay');

        document.querySelectorAll('[data-modal-close="mobile-sidebar"]').forEach(btn => {
            btn.addEventListener('click', () => {
                sidebar?.classList.add('hidden');
                overlay?.classList.add('hidden');
            });
        });

        overlay?.addEventListener('click', () => {
            sidebar?.classList.add('hidden');
            overlay?.classList.add('hidden');
        });
    });
</script>
