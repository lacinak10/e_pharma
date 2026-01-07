@props(['title' => 'Tableau de bord'])

<header class="flex items-center justify-between h-16 px-4 bg-white border-b border-gray-200">
    <div class="flex items-center">
        <button id="mobile-sidebar-button" class="p-1 mr-2 text-gray-500 rounded-md md:hidden focus:outline-none">
            <i class="fas fa-bars"></i>
        </button>
        <h2 class="text-lg font-semibold text-gray-800">{{ $title }}</h2>
    </div>

    <div class="flex items-center space-x-4">
        <button class="p-2 text-gray-500 rounded-full hover:bg-gray-100 focus:outline-none" type="button">
            <i class="fas fa-bell"></i>
        </button>

        <div class="flex items-center space-x-2">
            <img src="https://picsum.photos/32?random=1" alt="Profile" class="w-8 h-8 rounded-full">
            <span class="hidden md:inline-block text-sm font-medium">
                {{ auth()->user()->name ?? 'Pharmacie ABC' }}
            </span>
        </div>
    </div>
</header>
