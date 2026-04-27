@extends('layouts.app')

@section('title', $medicine->name . ' — Catalogue')

@section('content')
@php
    $fmt = fn(int $amount) => number_format($amount, 0, ',', ' ') . ' FCFA';
@endphp

<div class="max-w-4xl mx-auto">
    <a href="{{ route('catalog.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-600 hover:underline mb-4">
        &larr; Retour au catalogue
    </a>

    <div class="bg-white rounded border p-6 mt-2">
        <div class="flex flex-col md:flex-row gap-6">
            @if($medicine->image_url)
                <img
                    src="{{ Storage::url($medicine->image_url) }}"
                    alt="{{ $medicine->name }}"
                    class="w-full md:w-48 h-48 object-cover rounded border"
                >
            @else
                <div class="w-full md:w-48 h-48 bg-gray-100 rounded border flex items-center justify-center">
                    <span class="text-gray-400 text-sm">Aucune image</span>
                </div>
            @endif

            <div class="flex-1">
                <h1 class="text-2xl font-bold text-gray-900">{{ $medicine->name }}</h1>

                @if($medicine->category)
                    <p class="text-sm text-gray-500 mt-1">Catégorie : {{ $medicine->category->name }}</p>
                @endif

                <div class="mt-3 flex items-center gap-4">
                    <span class="text-2xl font-extrabold text-gray-900">{{ $fmt((int)$medicine->price) }}</span>

                    @if($medicine->stock > 0)
                        <span class="text-sm text-green-700 font-semibold">En stock ({{ $medicine->stock }})</span>
                    @else
                        <span class="text-sm text-red-700 font-semibold">Rupture de stock</span>
                    @endif
                </div>

                @if($medicine->description)
                    <div class="mt-4 text-gray-700 text-sm leading-relaxed">
                        {!! nl2br(e($medicine->description)) !!}
                    </div>
                @endif

                <div class="mt-6">
                    @auth
                        @if(auth()->user()->isClient() && $medicine->stock > 0)
                            <form method="POST" action="{{ route('store.cart.add', $medicine) }}" class="flex items-center gap-3">
                                @csrf
                                <input
                                    type="number"
                                    name="qty"
                                    min="1"
                                    max="{{ min(99, $medicine->stock) }}"
                                    value="1"
                                    class="border rounded px-3 py-2 w-20 text-sm"
                                >
                                <button type="submit" class="px-4 py-2 bg-gray-900 text-white rounded text-sm hover:bg-gray-800">
                                    Ajouter au panier
                                </button>
                            </form>
                        @elseif(!auth()->user()->isClient())
                            <p class="text-sm text-gray-500">Connecté en <b>{{ auth()->user()->role }}</b> : le panier est réservé au client.</p>
                        @else
                            <p class="text-sm text-red-600">Ce produit est épuisé.</p>
                        @endif
                    @else
                        <p class="text-sm text-gray-600">
                            <a href="{{ route('login') }}" class="underline">Connecte-toi</a> pour commander.
                        </p>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
