@props(['id' => 'modal', 'title' => 'Modal'])

<div id="{{ $id }}" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" data-modal-overlay="{{ $id }}"></div>

    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 overflow-hidden">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">{{ $title }}</h3>
                <button type="button" class="text-gray-500 hover:text-gray-700" data-modal-close="{{ $id }}">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="p-4">
                {{ $slot }}
            </div>

            @if(isset($footer))
                <div class="p-4 border-t border-gray-200 flex justify-end gap-2 bg-gray-50">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
