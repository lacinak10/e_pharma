@props(['messages'])

@if($messages)
    <ul {{ $attributes->merge(['class' => 'ep-error']) }} style="margin:0;padding-left:1rem;list-style:none">
        @foreach((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
