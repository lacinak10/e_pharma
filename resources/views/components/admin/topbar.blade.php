@props(['title' => 'Tableau de bord'])

@php
    $user = auth()->user();
    $role = $user->role ?? 'admin';
    $roleLabel = match($role) {
        'manager' => 'Manager',
        'courier' => 'Livreur',
        'admin'   => 'Administrateur',
        default  => 'Utilisateur'
    };
    $roleColor = match($role) {
        'manager' => 'bg-purple-100 text-purple-700',
        'courier' => 'bg-indigo-100 text-indigo-700',
        'admin'   => 'bg-blue-100 text-blue-700',
        default   => 'bg-gray-100 text-gray-700'
    };

    $notificationCount = $user->unreadNotifications->count();
    $notifications = $user->unreadNotifications->take(5)->map(fn($n) => [
        ‘id’      => $n->id,
        ‘type’    => $n->data[‘type’]    ?? ‘system’,
        ‘title’   => $n->data[‘title’]   ?? ‘Notification’,
        ‘message’ => $n->data[‘message’] ?? ‘’,
        ‘time’    => $n->created_at->diffForHumans(),
        ‘href’    => $n->data[‘url’]     ?? url(‘/admin/notifications’),
        ‘icon’    => $n->data[‘icon’]    ?? ‘fa-bell’,
        ‘color’   => $n->data[‘color’]   ?? ‘gray’,
    ]);
@endphp

<header class="flex items-center justify-between h-16 px-4 md:px-6 bg-white border-b border-gray-200 shadow-sm">
    <div class="flex items-center gap-4">
        <button id="mobile-sidebar-button"
                class="p-2 text-gray-600 rounded-lg md:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                aria-label="Ouvrir le menu latéral">
            <i class="fa-solid fa-bars text-lg"></i>
        </button>

        <div>
            <h1 class="text-xl font-bold text-gray-900">{{ $title }}</h1>
            <div class="flex items-center gap-2 mt-1">
                <span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-full {{ $roleColor }}">
                    {{ $roleLabel }}
                </span>
                <span class="text-xs text-gray-500">• Bienvenue, {{ $user->name }}</span>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <!-- Notifications Dropdown (Alpine.js) -->
        <div x-data="{ open: false }" @click.outside="open = false" class="relative">
            <button @click="open = !open"
                    class="relative p-3 text-gray-600 rounded-xl hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                    aria-label="Notifications ({{ $notificationCount }} non lues)"
                    aria-haspopup="true"
                    :aria-expanded="open">
                <i class="fa-regular fa-bell text-xl"></i>
                @if($notificationCount > 0)
                    <span class="absolute top-1.5 right-1.5 w-3 h-3 bg-red-500 rounded-full ring-2 ring-white animate-pulse"></span>
                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold rounded-full h-5 min-w-5 flex items-center justify-center">
                        {{ $notificationCount > 99 ? '99+' : $notificationCount }}
                    </span>
                @endif
            </button>

            <!-- Dropdown Panel -->
            <div x-show="open"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute right-0 mt-3 w-96 bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden z-50"
                 style="display: none;">
                <!-- Header -->
                <div class="flex items-center justify-between p-5 border-b border-gray-100">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Notifications</h3>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ $notificationCount > 0 ? "$notificationCount non lue(s)" : 'Aucune nouvelle notification' }}
                        </p>
                    </div>
                    @if($notificationCount > 0)
                        <form method="POST" action="{{ route('admin.notifications.markAllAsRead') }}">
                            @csrf
                            <button type="submit" class="text-sm font-medium text-blue-600 hover:text-blue-700">
                                Tout marquer comme lu
                            </button>
                        </form>
                    @endif
                </div>

                <!-- Liste des notifications -->
                <div class="max-h-96 overflow-y-auto">
                    @if($notificationCount === 0)
                        <div class="p-10 text-center">
                            <div class="mx-auto w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                                <i class="fa-regular fa-bell text-2xl text-gray-400"></i>
                            </div>
                            <p class="text-gray-600 font-medium">Tout est calme</p>
                            <p class="text-sm text-gray-500 mt-1">Aucune notification pour le moment.</p>
                        </div>
                    @else
                        <ul class="divide-y divide-gray-100">
                            @foreach($notifications as $n)
                                @php
                                    $colorClasses = match($n['color']) {
                                        'blue'    => 'bg-blue-100 text-blue-700 ring-blue-200',
                                        'indigo'  => 'bg-indigo-100 text-indigo-700 ring-indigo-200',
                                        'yellow'  => 'bg-yellow-100 text-yellow-700 ring-yellow-200',
                                        'green'   => 'bg-green-100 text-green-700 ring-green-200',
                                        default   => 'bg-gray-100 text-gray-700 ring-gray-200'
                                    };
                                @endphp
                                <li class="hover:bg-gray-50 transition">
                                    <form method="POST" action="{{ route('admin.notifications.markAsRead', $n['id']) }}" class="contents">
                                        @csrf
                                        <a href="{{ $n['href'] }}"
                                           onclick="this.closest('form').submit(); return false;"
                                           class="flex items-start gap-4 p-4">
                                            <div class="flex-shrink-0 w-11 h-11 rounded-xl {{ $colorClasses }} flex items-center justify-center ring-4 ring-white">
                                                <i class="fa-solid {{ $n['icon'] }} text-base"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="font-semibold text-gray-900 text-sm">{{ $n['title'] }}</p>
                                                <p class="text-sm text-gray-600 mt-1 line-clamp-2">{{ $n['message'] }}</p>
                                                <p class="text-xs text-gray-400 mt-2">{{ $n['time'] }}</p>
                                            </div>
                                        </a>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <!-- Footer -->
                <div class="p-4 border-t border-gray-100 bg-gray-50 text-center">
                    <a href="{{ route('admin.notifications.index') }}"
                       class="text-sm font-semibold text-blue-600 hover:text-blue-700">
                        Voir toutes les notifications →
                    </a>
                </div>
            </div>
        </div>

        <!-- Profil Dropdown (inchangé) -->
        <div x-data="{ open: false }" @click.outside="open = false" class="relative">
            <button @click="open = !open"
                    class="flex items-center gap-3 p-2 rounded-xl hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                <img src="{{ $user->avatar ?? 'https://picsum.photos/40?random=' . $user->id }}"
                     alt="Photo de profil"
                     class="w-9 h-9 rounded-full object-cover ring-2 ring-gray-200">
                <div class="hidden md:block text-left">
                    <p class="text-sm font-semibold text-gray-900">{{ $user->name }}</p>
                    <p class="text-xs text-gray-500">{{ $roleLabel }}</p>
                </div>
                <i class="fa-solid fa-chevron-down text-xs text-gray-500 hidden md:block transition" :class="{'rotate-180': open}"></i>
            </button>

            <div x-show="open" x-transition
                 class="absolute right-0 mt-2 w-64 bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden z-50">
                <div class="p-4 border-b border-gray-100">
                    <p class="font-medium text-gray-900">{{ $user->name }}</p>
                    <p class="text-sm text-gray-500">{{ $user->email }}</p>
                </div>
                <nav class="py-2">
                    <a href="{{ url('/admin/profile') }}" class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50">
                        <i class="fa-regular fa-user w-5"></i> Mon profil
                    </a>
                    <a href="{{ url('/admin/settings') }}" class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50">
                        <i class="fa-solid fa-gear w-5"></i> Paramètres
                    </a>
                </nav>
                <div class="border-t border-gray-100">
                    <form method="POST" action="{{ route('logout') }}" class="p-2">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 text-sm text-red-600 hover:bg-red-50 rounded-lg">
                            <i class="fa-solid fa-right-from-bracket w-5"></i> Déconnexion
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
