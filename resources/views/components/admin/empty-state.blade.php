@props([
    'title' => 'Aucun résultat',
    'description' => null,
    'icon' => 'fas fa-circle-info',
])

<div class="text-center py-6">
    <div class="mx-auto w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center text-gray-500">
        <i class="{{ $icon }}"></i>
    </div>
    <div class="mt-3 font-semibold text-gray-800">{{ $title }}</div>
    @if($description)
        <div class="mt-1 text-sm text-gray-500">{{ $description }}</div>
    @endif
</div>
