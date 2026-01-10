@props(['title' => 'Aucun résultat', 'subtitle' => ''])

<div class="bg-white border border-gray-100 rounded-2xl p-10 text-center">
    <div class="w-14 h-14 rounded-2xl bg-gray-100 mx-auto flex items-center justify-center">
        <i class="fa-regular fa-face-frown text-2xl text-gray-400"></i>
    </div>
    <h3 class="mt-4 text-lg font-bold text-gray-900">{{ $title }}</h3>
    @if($subtitle)
        <p class="mt-2 text-sm text-gray-600">{{ $subtitle }}</p>
    @endif
</div>
