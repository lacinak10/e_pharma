@props([
    'title' => null,
    'actions' => null,
])

<div class="bg-white rounded-lg shadow">
    @if($title || $actions)
        <div class="p-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">{{ $title }}</h3>
            <div>
                {{ $actions }}
            </div>
        </div>
    @endif

    <div {{ $attributes->merge(['class' => 'p-4']) }}>
        {{ $slot }}
    </div>
</div>
