@extends('layouts.store')

@section('title', 'Panier — E-PHARMA')

@section('content')
<section class="max-w-7xl mx-auto px-4 py-10">
    <div class="flex items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold">Votre panier</h1>
            <p class="text-gray-600 mt-1">Vérifie les quantités avant de commander.</p>
        </div>

        @if(!empty($cart))
            <form method="POST" action="{{ route('store.cart.clear') }}">
                @csrf
                @method('DELETE')
                <x-store.button variant="outline" type="submit">
                    <i class="fa-regular fa-trash-can"></i> Vider
                </x-store.button>
            </form>
        @endif
    </div>

    @if(empty($cart))
        <div class="mt-8">
            <x-store.empty title="Panier vide" subtitle="Ajoute des médicaments depuis le catalogue." />
            <div class="mt-6 text-center">
                <a href="{{ route('store.medicines.index') }}" class="inline-flex px-6 py-3 rounded-2xl bg-blue-600 text-white font-bold hover:bg-blue-700">
                    Aller au catalogue
                </a>
            </div>
        </div>
    @else
        <div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h3 class="text-lg font-extrabold">Articles</h3>

                <div class="divide-y divide-gray-100 mt-4">
                    @foreach($cart as $item)
                        <x-store.cart-row :item="$item" />
                    @endforeach
                </div>
            </div>

            <x-store.cart-summary :cart="$cart">
                <div class="mt-6 space-y-3">
                    <a href="{{ route('store.checkout.create') }}" class="block">
                        <x-store.button class="w-full" variant="primary" type="button">
                            <i class="fa-solid fa-lock"></i> Passer à la commande
                        </x-store.button>
                    </a>

                    <a href="{{ route('store.medicines.index') }}" class="block">
                        <x-store.button class="w-full" variant="outline" type="button">
                            Continuer vos achats
                        </x-store.button>
                    </a>
                </div>
            </x-store.cart-summary>
        </div>
    @endif
</section>
@endsection
