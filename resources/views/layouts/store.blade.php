<!DOCTYPE html>
<html lang="fr" class="ep-root">
<head>
    @include('layouts.partials.head')

    @stack('head')
</head>

<body class="ep-root ep-has-tabbar">
<a href="#ep-content" class="ep-sr-only">Aller au contenu</a>

<x-store.header />

@if($currentOrder ?? null)
    {{-- Bandeau persistant : le client garde sa commande sous les yeux --}}
    <div style="background:var(--ep-ink)">
        <div class="ep-shell" style="padding-block:.25rem">
            <x-ep.order-banner :order="$currentOrder" square style="background:transparent;padding-inline:0" />
        </div>
    </div>
@endif

<main id="ep-content" style="min-height:60vh">
    <div class="ep-shell" style="padding-top:1rem">
        <x-ep.flash />
    </div>

    @yield('content')
</main>

<x-store.footer />

<x-store.tabbar />

@stack('scripts')
</body>
</html>
