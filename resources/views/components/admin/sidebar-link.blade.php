@props([
    'href' => '#',
    'icon' => 'fas fa-circle',
    'label' => '',
    'active' => false,
])

<a href="{{ $href }}"
   class="flex items-center px-4 py-2 text-sm font-medium rounded-lg transition
   {{ $active ? 'text-white bg-primary' : 'text-gray-700 hover:bg-gray-100' }}">
    <i class="{{ $icon }} mr-3"></i>
    {{ $label }}
</a>
