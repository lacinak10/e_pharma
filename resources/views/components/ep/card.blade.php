@props([
    'title'  => null,
    'meta'   => null,
    'dark'   => false,
    'flush'  => false,   // pas de padding interne (tableaux, listes)
])

<section {{ $attributes->merge(['class' => 'ep-card' . ($dark ? ' ep-card--dark' : '')]) }}>
    @if($title || isset($actions))
        <header class="ep-card__head">
            @if($title)<h2 class="ep-card__title">{{ $title }}</h2>@endif
            @if($meta)<span class="ep-mono ep-small">{{ $meta }}</span>@endif
            @isset($actions)
                <div class="ep-spacer ep-row ep-row--nowrap">{{ $actions }}</div>
            @endisset
        </header>
    @endif

    @if($flush)
        {{ $slot }}
    @else
        <div class="ep-card__body">{{ $slot }}</div>
    @endif

    @isset($footer)
        <footer class="ep-card__foot">{{ $footer }}</footer>
    @endisset
</section>
