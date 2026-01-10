@props(['item'])

<div class="flex gap-4 py-4">
    <div class="w-20 h-20 rounded-xl bg-gray-50 flex items-center justify-center overflow-hidden border border-gray-100">
        @if($item['image_url'])
            <img src="{{ $item['image_url'] }}" class="h-16 object-contain" alt="{{ $item['name'] }}">
        @else
            <i class="fa-solid fa-pills text-3xl text-gray-300"></i>
        @endif
    </div>

    <div class="flex-1 min-w-0">
        <div class="flex items-start justify-between gap-3">
            <div>
                <div class="font-bold text-gray-900 line-clamp-1">{{ $item['name'] }}</div>
                <div class="text-sm text-gray-500 mt-1">
                    {{ number_format((int)$item['price'], 0, ',', ' ') }} FCFA
                </div>
            </div>
        </div>

        <div class="mt-3 flex items-center justify-between gap-3">
            <form method="POST" action="{{ route('store.cart.update', $item['id']) }}" class="flex items-center gap-2">
                @csrf
                @method('PATCH')
                <label class="text-xs text-gray-500">Qté</label>
                <input
                    type="number"
                    name="qty"
                    min="1"
                    max="99"
                    value="{{ (int)$item['qty'] }}"
                    class="w-20 rounded-xl border border-gray-200 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-600 focus:border-transparent"
                >
                <x-store.button variant="outline" type="submit">
                    Mettre à jour
                </x-store.button>
            </form>

            <form method="POST" action="{{ route('store.cart.remove', $item['id']) }}">
                @csrf
                @method('DELETE')
                <x-store.button variant="danger" type="submit">
                    <i class="fa-solid fa-trash"></i>
                </x-store.button>
            </form>
        </div>
    </div>

    <div class="text-right font-extrabold text-gray-900">
        {{ number_format(((int)$item['price'] * (int)$item['qty']), 0, ',', ' ') }} FCFA
    </div>
</div>
