@props(['name' => null])

<select
    @if($name) name="{{ $name }}" id="{{ $name }}" @endif
    {{ $attributes->merge([
        'class' => 'block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary sm:text-sm rounded-md bg-white'
    ]) }}
>
    {{ $slot }}
</select>
