@props([
    'href'   => '#',
    'icon'   => 'fas fa-circle',
    'label'  => '',
    'active' => false,
    'badge'  => null,
])

<a href="{{ $href }}"
   class="flex items-center px-4 py-2 text-sm font-medium rounded-lg transition
   {{ $active ? 'text-white bg-primary' : 'text-gray-700 hover:bg-gray-100' }}">
    <i class="{{ $icon }} mr-3"></i>
    <span class="flex-1">{{ $label }}</span>
    @if($badge)
        <span class="ml-auto inline-flex items-center justify-center min-w-5 h-5 px-1 text-[10px] font-bold rounded-full
            {{ $active ? 'bg-white text-blue-600' : 'bg-red-500 text-white' }}">
            {{ $badge > 99 ? '99+' : $badge }}
        </span>
    @endif
</a>
