@props([
    'label' => '',
    'value' => '',
    'icon' => 'fas fa-circle',
    'valueClass' => 'text-primary',
    'iconWrapClass' => 'bg-blue-100 text-primary',
])

<div class="bg-white rounded-lg shadow p-4">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-gray-500">{{ $label }}</p>
            <p class="text-2xl font-bold {{ $valueClass }}">{{ $value }}</p>
        </div>
        <div class="p-3 rounded-full {{ $iconWrapClass }}">
            <i class="{{ $icon }}"></i>
        </div>
    </div>
</div>
