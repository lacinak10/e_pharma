@props([
    'id' => 'modal',
    'title' => 'Modal',
])

<div id="{{ $id }}" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black opacity-50" data-modal-overlay="{{ $id }}"></div>

    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
            <div class="p-4 border-b border-gray-200 relative">
                <h3 class="text-lg font-semibold text-gray-800">{{ $title }}</h3>
                <button class="absolute top-4 right-4 text-gray-500 hover:text-gray-700" type="button" data-modal-close="{{ $id }}">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="p-4">
                {{ $slot }}
            </div>

            @if(isset($footer))
                <div class="p-4 border-t border-gray-200 flex justify-end space-x-3">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
