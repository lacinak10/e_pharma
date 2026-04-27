@props(['label' => null, 'name' => null, 'type' => 'text'])

<label class="block">
    @if($label)
        <span class="block text-sm font-semibold text-gray-700 mb-1">{{ $label }}</span>
    @endif

    <input
        type="{{ $type }}"
        @if($name) name="{{ $name }}" @endif
        {{ $attributes->merge(['class' => 'w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent']) }}
    />
    @error($name)
        <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span>
    @enderror
</label>
