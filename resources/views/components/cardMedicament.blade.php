<div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition">
    <div class="p-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3 class="font-bold text-lg text-gray-800">{{ $title }}</h3>
                <p class="text-sm text-gray-500 mt-1">{{ $description }}</p>
            </div>

            <span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-700">
                {{ $status }}
            </span>
        </div>

        <div class="mt-4 flex items-center justify-between">
            <div class="text-blue-600 font-bold text-lg">{{ $price }} FCFA</div>

            <a href="/medicaments" class="text-sm text-blue-500 hover:text-blue-700 font-medium">
                Voir
            </a>
        </div>
    </div>
</div>
