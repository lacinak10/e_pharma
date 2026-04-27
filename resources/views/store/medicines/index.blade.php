@extends('layouts.store')

@section('title', 'Catalogue — E-PHARMA')

@section('content')
<section class="max-w-7xl mx-auto px-4 py-10">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold">Catalogue</h1>
            <p class="text-gray-600 mt-1">Recherche rapide + filtres.</p>
        </div>

        <form class="w-full md:w-auto flex flex-col sm:flex-row gap-3" method="GET" action="{{ route('store.medicines.index') }}">
            <div class="relative flex-1 min-w-[260px]">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input
                    name="q"
                    value="{{ $q }}"
                    placeholder="Rechercher un médicament..."
                    class="w-full pl-11 pr-4 py-3 rounded-2xl border border-gray-200 bg-white focus:ring-2 focus:ring-blue-600 focus:border-transparent"
                >
            </div>

            <select name="category" class="py-3 rounded-2xl border border-gray-200 bg-white px-4 focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                <option value="">Toutes catégories</option>
                @foreach($categories as $c)
                    <option value="{{ $c->slug }}" @selected($category === $c->slug)>{{ $c->name }}</option>
                @endforeach
            </select>

            <x-store.button type="submit" variant="primary">
                <i class="fa-solid fa-filter"></i> Filtrer
            </x-store.button>
        </form>
    </div>

    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @forelse($medicines as $medicine)
            <x-store.product-card :medicine="$medicine" />
        @empty
            <x-store.empty title="Aucun résultat" subtitle="Essaie un autre mot-clé ou une autre catégorie." />
        @endforelse
    </div>

    <div class="mt-8">
        {{ $medicines->links() }}
    </div>
</section>
@endsection
