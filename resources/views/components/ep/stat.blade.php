@props([
    'label',
    'value',
    'delta' => null,
    'color' => '#0E5C43',
    'href'  => null,
])

@php $tag = $href ? 'a' : 'div'; @endphp

<{{ $tag }} @if($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => 'ep-stat']) }}
    style="border-top-color: {{ $color }}{{ $href ? ';text-decoration:none;color:inherit' : '' }}">
    <p class="ep-stat__label">{{ $label }}</p>
    <p class="ep-stat__row">
        <span class="ep-stat__value" style="color: {{ $color }}">{{ $value }}</span>
        @if($delta)<span class="ep-stat__delta">{{ $delta }}</span>@endif
    </p>
</{{ $tag }}>
