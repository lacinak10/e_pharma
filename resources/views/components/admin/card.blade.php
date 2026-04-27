@props(['title' => null, 'actions' => null])

<div class="bg-white rounded-lg shadow">
    @if($title || $actions)
        <div class="p-4 border-b border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <h3 class="text-lg font-semibold text-gray-800">{{ $title }}</h3>
            <div>{{ $actions }}</div>
        </div>
    @endif

    <div {{ $attributes->merge(['class' => 'p-4']) }}>
        {{ $slot }}
    </div>
</div>
