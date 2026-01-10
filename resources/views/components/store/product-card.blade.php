@props(['medicine'])

@php
    $status = $medicine->status ?? 'En stock';

    $statusConfig = match($status) {
        'Épuisé'      => ['color' => 'red',   'text' => 'Indisponible',   'buttonVariant' => 'outline', 'disabled' => true],
        'Stock faible' => ['color' => 'yellow','text' => 'Ajouter au panier','buttonVariant' => 'warning', 'disabled' => false],
        default        => ['color' => 'green', 'text' => 'Ajouter au panier','buttonVariant' => 'primary', 'disabled' => false],
    };

    $stockText = $medicine->stock <= 0 ? '0' : number_format($medicine->stock, 0, ',', ' ');
    $priceFormatted = number_format((int) $medicine->price, 0, ',', ' ') . ' FCFA';
@endphp

<div class="group bg-white rounded-2xl border border-gray-200 shadow-sm hover:shadow-lg hover:border-gray-300 transition-all duration-300 overflow-hidden">
    <!-- Image -->
    <a href="{{ route('store.medicines.show', $medicine) }}" class="block aspect-[4/3] bg-gray-50 relative overflow-hidden">
        @if($medicine->image_url)
            <img
                src="{{ $medicine->image_url }}"
                alt="{{ $medicine->name }}"
                class="w-full h-full object-contain p-6 transition-transform duration-500 group-hover:scale-105"
                loading="lazy"
            >
        @else
            <div class="w-full h-full flex items-center justify-center">
                <i class="fa-solid fa-pills text-6xl text-gray-200"></i>
            </div>
        @endif

        <!-- Badge statut en overlay -->
        <div class="absolute top-3 right-3">
            <x-store.badge :variant="$statusConfig['color']" size="sm">
                {{ $status }}
            </x-store.badge>
        </div>
    </a>

    <!-- Contenu -->
    <div class="p-5">
        <div class="min-w-0">
            <a href="{{ route('store.medicines.show', $medicine) }}"
               class="block font-semibold text-gray-900 text-lg leading-tight line-clamp-2 hover:text-blue-700 transition-colors">
                {{ $medicine->name }}
            </a>

            <p class="mt-1 text-sm text-gray-500 line-clamp-1">
                {{ $medicine->category?->name ?? 'Catégorie non définie' }}
            </p>
        </div>

        <div class="mt-4 flex items-center justify-between">
            <div class="text-xl font-bold text-blue-700">
                {{ $priceFormatted }}
            </div>

            <div class="text-sm {{ $medicine->stock <= 5 ? 'text-red-600 font-medium' : 'text-gray-600' }}">
                Stock : {{ $stockText }}
            </div>
        </div>

        <!-- Bouton Ajouter au panier -->
        <form method="POST" action="{{ route('store.cart.add', $medicine) }}" class="mt-5">
            @csrf
            <input type="hidden" name="quantity" value="1">

            <x-store.button
                type="submit"
                class="w-full justify-center gap-2"
                :variant="$statusConfig['buttonVariant']"
                :disabled="$statusConfig['disabled']"
            >
                <i class="fa-solid fa-cart-plus text-base"></i>
                {{ $statusConfig['text'] }}
            </x-store.button>
        </form>
    </div>
</div>
