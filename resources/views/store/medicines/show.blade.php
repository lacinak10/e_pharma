@extends('layouts.store')

@section('title', $medicine->name . ' — E-PHARMA')

@section('content')
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-16">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-12">
        <!-- Colonne image + galerie (prévue pour futur) -->
        <div class="space-y-6">
            <div class="bg-white rounded-3xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="aspect-[4/3] md:aspect-[5/4] lg:aspect-square bg-gray-50 flex items-center justify-center p-8">
                    @if($medicine->image_url)
                        <img
                            src="{{ $medicine->image_src }}"
                            alt="{{ $medicine->name }}"
                            class="max-h-full w-auto object-contain transition-transform duration-700 hover:scale-105"
                            loading="eager"
                        >
                    @else
                        <div class="text-center">
                            <i class="fa-solid fa-pills text-8xl text-gray-200"></i>
                            <p class="mt-4 text-sm text-gray-400">Aucune image disponible</p>
                        </div>
                    @endif
                </div>

                <!-- Badge statut en overlay (plus visible) -->
                <div class="absolute top-6 right-6 z-10">
                    @php
                        $statusConfig = match($medicine->status ?? 'En stock') {
                            'Épuisé'      => ['variant' => 'red',    'text' => 'Épuisé'],
                            'Stock faible' => ['variant' => 'yellow', 'text' => 'Stock faible'],
                            default        => ['variant' => 'green',  'text' => 'En stock'],
                        };
                    @endphp
                    <x-store.badge :variant="$statusConfig['variant']" size="lg">
                        {{ $statusConfig['text'] }}
                    </x-store.badge>
                </div>
            </div>

            <!-- Miniatures futures (placeholder) -->
            <div class="hidden lg:flex gap-4">
                <div class="w-20 h-20 bg-gray-100 rounded-xl border border-gray-200 flex items-center justify-center cursor-pointer hover:border-blue-500 transition">
                    <i class="fa-solid fa-image text-gray-400"></i>
                </div>
                <!-- Ajouter d'autres miniatures quand plusieurs images seront disponibles -->
            </div>
        </div>

        <!-- Colonne informations -->
        <div class="space-y-8">
            <div>
                <a href="{{ route('store.medicines.index') }}"
                   class="inline-flex items-center gap-2 text-sm font-medium text-blue-700 hover:text-blue-800 transition">
                    <i class="fa-solid fa-arrow-left text-xs"></i>
                    Retour aux médicaments
                </a>

                <h1 class="mt-4 text-3xl md:text-4xl font-bold text-gray-900 leading-tight">
                    {{ $medicine->name }}
                </h1>

                <div class="mt-3 flex flex-wrap items-center gap-4">
                    @if($medicine->category?->name)
                        <span class="text-base text-gray-600">
                            {{ $medicine->category->name }}
                        </span>
                    @endif

                    <span class="text-sm text-gray-500">
                        Stock :
                        <span class="{{ $medicine->stock <= 5 ? 'text-red-600 font-medium' : 'text-gray-700' }}">
                            {{ number_format($medicine->stock, 0, ',', ' ') }}
                        </span>
                    </span>
                </div>
            </div>

            <!-- Prix & Ajout panier -->
            <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-7">
                <div class="flex items-baseline gap-3">
                    <span class="text-4xl md:text-5xl font-extrabold text-blue-700">
                        {{ number_format((int) $medicine->price, 0, ',', ' ') }}
                    </span>
                    <span class="text-2xl font-bold text-blue-700">FCFA</span>
                </div>

                <form method="POST" action="{{ route('store.cart.add', $medicine) }}" class="mt-8">
                    @csrf

                    <div class="flex flex-col sm:flex-row gap-4 items-end">
                        <x-store.input
                            label="Quantité"
                            name="qty"
                            type="number"
                            min="1"
                            max="{{ min(99, max(1, $medicine->stock)) }}"
                            value="1"
                            class="sm:w-32"
                            :disabled="$medicine->stock <= 0"
                        />

                        <x-store.button
                            type="submit"
                            class="w-full sm:w-auto min-w-[180px] justify-center gap-2"
                            :variant="$medicine->stock <= 0 ? 'outline' : 'primary'"
                            :disabled="$medicine->stock <= 0"
                        >
                            <i class="fa-solid fa-cart-plus"></i>
                            {{ $medicine->stock <= 0 ? 'Indisponible' : 'Ajouter au panier' }}
                        </x-store.button>
                    </div>
                </form>
            </div>

            <!-- Description -->
            <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-7">
                <h3 class="text-xl font-bold text-gray-900">Description</h3>
                <div class="mt-4 prose prose-sm sm:prose text-gray-600 max-w-none">
                    {!! nl2br(e($medicine->description ?: "Aucune description détaillée disponible pour le moment.")) !!}
                </div>
            </div>
        </div>
    </div>

    <!-- Produits similaires -->
    <div class="mt-16 lg:mt-20">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">Produits similaires</h2>
            @if($medicine->category?->slug)
                <a href="{{ route('store.medicines.index', ['category' => $medicine->category->slug]) }}"
                   class="text-sm font-semibold text-blue-700 hover:text-blue-800 flex items-center gap-1 transition">
                    Voir toute la catégorie →
                </a>
            @endif
        </div>

        <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @forelse($related as $relatedMedicine)
                <x-store.product-card :medicine="$relatedMedicine" />
            @empty
                <div class="col-span-full">
                    <x-store.empty
                        title="Aucun produit similaire trouvé"
                        subtitle="Découvrez d'autres catégories !"
                    />
                </div>
            @endforelse
        </div>
    </div>
</section>
@endsection
