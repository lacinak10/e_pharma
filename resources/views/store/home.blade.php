@extends('layouts.store')

@section('title', 'E-PHARMA — Pharmacie en ligne')

@section('content')
<section class="pharmacy-gradient text-white">
    <div class="max-w-7xl mx-auto px-4 py-14 grid grid-cols-1 md:grid-cols-2 gap-10 items-center">
        <div>
            <h1 class="text-4xl md:text-5xl font-extrabold leading-tight">
                Vos médicaments livrés <span class="text-blue-200">en moins de 2 heures</span>
            </h1>
            <p class="mt-4 text-white/90 text-lg">
                Commandez en ligne, paiement flexible, suivi en temps réel.
            </p>

            <div class="mt-6 flex flex-col sm:flex-row gap-3">
                <a href="{{ route('store.medicines.index') }}"
                   class="px-6 py-3 rounded-2xl bg-white text-blue-700 font-bold text-center hover:bg-blue-50">
                    Rechercher un médicament
                </a>
                <a href="{{ route('store.prescriptions.create') }}"
                   class="px-6 py-3 rounded-2xl border border-white/40 font-semibold text-center hover:bg-white/10">
                    Envoyer une ordonnance
                </a>
            </div>

            <div class="mt-8 grid grid-cols-2 gap-3 text-sm">
                <div class="bg-white/10 rounded-2xl p-4 border border-white/10">
                    <div class="font-bold">Paiement</div>
                    <div class="text-white/80 mt-1">Cash • MoMo • Carte</div>
                </div>
                <div class="bg-white/10 rounded-2xl p-4 border border-white/10">
                    <div class="font-bold">Support</div>
                    <div class="text-white/80 mt-1">WhatsApp 7j/7</div>
                </div>
            </div>
        </div>

        <div class="flex justify-center">
            <img class="rounded-3xl shadow-2xl w-full max-w-md object-cover"
                 src="https://images.unsplash.com/photo-1587854692152-cbe660dbde88?auto=format&fit=crop&w=1200&q=80"
                 alt="Pharmacie">
        </div>
    </div>
</section>

<section class="max-w-7xl mx-auto px-4 py-12">
    <div class="flex items-end justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold">Produits populaires</h2>
            <p class="text-gray-600 mt-1">Ajoute au panier en 1 clic.</p>
        </div>
        <a href="{{ route('store.medicines.index') }}" class="text-blue-700 font-semibold hover:underline">
            Voir tout →
        </a>
    </div>

    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @forelse($featured as $medicine)
            <x-store.product-card :medicine="$medicine" />
        @empty
            <x-store.empty title="Aucun médicament disponible" subtitle="Ajoute des médicaments depuis l’admin." />
        @endforelse
    </div>

    <div class="mt-12 bg-white border border-gray-100 rounded-3xl p-8">
        <h3 class="text-xl font-extrabold">Comment ça marche</h3>
        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="p-6 rounded-2xl bg-gray-50 border border-gray-100">
                <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <div class="mt-4 font-bold">1) Rechercher</div>
                <p class="text-sm text-gray-600 mt-2">Nom, catégorie, disponibilité.</p>
            </div>
            <div class="p-6 rounded-2xl bg-gray-50 border border-gray-100">
                <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center">
                    <i class="fa-solid fa-cart-shopping"></i>
                </div>
                <div class="mt-4 font-bold">2) Commander</div>
                <p class="text-sm text-gray-600 mt-2">Panier, livraison, paiement.</p>
            </div>
            <div class="p-6 rounded-2xl bg-gray-50 border border-gray-100">
                <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center">
                    <i class="fa-solid fa-truck"></i>
                </div>
                <div class="mt-4 font-bold">3) Suivre</div>
                <p class="text-sm text-gray-600 mt-2">Timeline de la commande.</p>
            </div>
        </div>
    </div>
</section>
@endsection
