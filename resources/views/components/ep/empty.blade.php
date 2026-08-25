@props([
    'title' => 'Rien en cours.',
    'text'  => 'Cherchez un médicament ou envoyez une ordonnance.',
])

<div {{ $attributes->merge(['class' => 'ep-empty']) }}>
    <p class="ep-empty__title">{{ $title }}</p>
    <p class="ep-empty__text">{{ $text }}</p>
    @isset($actions)
        <div class="ep-row" style="justify-content:center;margin-top:1.25rem">{{ $actions }}</div>
    @endisset
</div>
