@extends('layouts.app')

@section('content')
@php
    $fmt = fn(int $amount) => number_format($amount, 0, ',', ' ') . ' FCFA';
@endphp

<div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-bold">Catalogue des médicaments</h1>

    <form method="GET" action="{{ route('catalog.index') }}" class="flex gap-2">
        <input
            type="text"
            name="q"
            value="{{ $q ?? '' }}"
            placeholder="Rechercher..."
            class="border rounded px-3 py-2 w-64"
        >
        <button class="px-4 py-2 rounded bg-gray-900 text-white" type="submit">OK</button>
    </form>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($medicines as $m)
        <div class="bg-white rounded border p-4">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <a class="font-semibold hover:underline" href="{{ route('catalog.show', $m) }}">
                        {{ $m->name }}
                    </a>
                    <div class="text-sm text-gray-600 mt-1">
                        Prix: <span class="font-semibold">{{ $fmt($m->price) }}</span>
                    </div>
                    <div class="text-sm mt-1">
                        Stock:
                        @if($m->stock > 0)
                            <span class="text-green-700 font-semibold">{{ $m->stock }}</span>
                        @else
                            <span class="text-red-700 font-semibold">Rupture</span>
                        @endif
                    </div>
                </div>
                @if($m->image_url)
                    <img class="w-14 h-14 object-cover rounded border" src="{{ $m->image_url }}" alt="{{ $m->name }}">
                @endif
            </div>

            <div class="text-sm text-gray-700 mt-3 line-clamp-3">
                {{ \Illuminate\Support\Str::limit($m->description ?? '', 120) }}
            </div>

            @auth
                @if(auth()->user()->isClient())
                    <form class="mt-4 flex items-center gap-2" method="POST" action="{{ route('cart.store') }}">
                        @csrf
                        <input type="hidden" name="medicine_id" value="{{ $m->id }}">
                        <input
                            type="number"
                            name="quantity"
                            min="1"
                            value="1"
                            class="border rounded px-2 py-2 w-20"
                            {{ $m->stock <= 0 ? 'disabled' : '' }}
                        >
                        <button
                            class="px-3 py-2 rounded bg-gray-900 text-white {{ $m->stock <= 0 ? 'opacity-40 cursor-not-allowed' : '' }}"
                            type="submit"
                            {{ $m->stock <= 0 ? 'disabled' : '' }}
                        >
                            Ajouter
                        </button>
                        <a class="text-sm hover:underline ml-auto" href="{{ route('cart.index') }}">Voir panier</a>
                    </form>
                @else
                    <div class="mt-4 text-xs text-gray-500">
                        Connecté en <b>{{ auth()->user()->role }}</b> : le panier est réservé au client.
                    </div>
                @endif
            @else
                <div class="mt-4 text-sm text-gray-600">
                    <a class="underline" href="{{ route('login') }}">Connecte-toi</a> pour commander.
                </div>
            @endauth
        </div>
    @empty
        <div class="bg-white rounded border p-4 col-span-full">
            Aucun médicament trouvé.
        </div>
    @endforelse
</div>

<div class="mt-6">
    {{ $medicines->links() }}
</div>
@endsection
