<!DOCTYPE html>
<html lang="fr" class="ep-root">
<head>
    @include('layouts.partials.head')
</head>

<body class="ep-root" style="background:var(--ep-bg)">
<div style="min-height:100vh;display:grid;grid-template-columns:1fr">
    <div style="display:flex;flex-direction:column">
        <header style="background:var(--ep-ink);padding:.875rem 0">
            <div class="ep-shell">
                <a href="{{ route('store.home') }}" class="ep-logo" style="color:#fff">
                    <img class="ep-logo__mark" src="{{ asset('assets/images/logo.jpeg') }}"
                         alt="" aria-hidden="true" width="34" height="34">
                    <span class="ep-logo__word" style="color:#fff">ePharma</span>
                </a>
            </div>
        </header>

        <main style="flex:1;display:flex;align-items:center;justify-content:center;padding:clamp(1.5rem,1rem + 3vw,4rem) 1rem">
            <div style="width:100%;max-width:440px">
                <p class="ep-eyebrow ep-eyebrow--green" style="margin-bottom:.75rem">
                    La disponibilité, avant le déplacement
                </p>

                <div class="ep-card" style="padding:clamp(1.5rem,1rem + 2vw,2rem)">
                    {{ $slot }}
                </div>

                <p class="ep-small" style="text-align:center;margin-top:1.25rem">
                    <a href="{{ route('store.home') }}">← Retour à la boutique</a>
                </p>
            </div>
        </main>
    </div>
</div>
</body>
</html>
