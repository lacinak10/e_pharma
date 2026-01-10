@props([
    'title' => '',
    'description' => '',
    'price' => '',
    'status' => 'En stock',
    'statusVariant' => 'green',
    'href' => '/medicaments',
])

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition">
    <div class="p-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3 class="font-bold text-lg text-gray-800">{{ $title }}</h3>
                <p class="text-sm text-gray-500 mt-1">{{ $description }}</p>
            </div>

            @php
                $badge = match($statusVariant){
                    'green' => 'bg-green-100 text-green-700',
                    'yellow'=> 'bg-yellow-100 text-yellow-700',
                    'red'   => 'bg-red-100 text-red-700',
                    default => 'bg-gray-100 text-gray-700',
                };
            @endphp

            <span class="text-xs px-2 py-1 rounded-full {{ $badge }}">
                {{ $status }}
            </span>
        </div>

        <div class="mt-4 flex items-center justify-between">
            <div class="text-blue-600 font-bold text-lg">{{ $price }} FCFA</div>

            <a href="{{ $href }}" class="text-sm text-blue-500 hover:text-blue-700 font-medium">
                Voir
            </a>
        </div>
    </div>
</div>
