@props(['status'])

@if($status)
    <p {{ $attributes->merge(['class' => 'ep-flash ep-flash--success']) }} style="margin-bottom:1rem">
        {{ $status }}
    </p>
@endif
