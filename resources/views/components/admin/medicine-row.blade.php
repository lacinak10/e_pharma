@props([
    'medicine',
    'price' => '',
    'statusLabel' => '',
    'statusVariant' => 'gray',
    'editUrl' => '#',
    'deleteUrl' => '#',
])

<tr>
    <td class="px-6 py-4 whitespace-nowrap">
        <input type="checkbox" class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
    </td>

    <td class="px-6 py-4 whitespace-nowrap">
        <div class="flex items-center">
            <div class="flex-shrink-0 h-10 w-10">
                <img class="h-10 w-10 rounded object-cover"
                     src="/storage/{{ $medicine->image_url ?? 'https://picsum.photos/40?random='.$medicine->id }}"
                     alt="{{ $medicine->name }}">
            </div>
            <div class="ml-4">
                <div class="text-sm font-medium text-gray-900">{{ $medicine->name }}</div>
                <div class="text-sm text-gray-500">REF: {{ $medicine->sku ?? 'N/A' }}</div>
            </div>
        </div>
    </td>

    <td class="px-6 py-4 whitespace-nowrap">
        <div class="text-sm text-gray-900">{{ $medicine->category ?? '-' }}</div>
    </td>

    <td class="px-6 py-4 whitespace-nowrap">
        <div class="text-sm text-gray-900">{{ $price }}</div>
    </td>

    <td class="px-6 py-4 whitespace-nowrap">
        <div class="text-sm text-gray-900">{{ (int)$medicine->stock }}</div>
    </td>

    <td class="px-6 py-4 whitespace-nowrap">
        <x-admin.badge :text="$statusLabel" :variant="$statusVariant" />
    </td>

    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
        <a href="{{ $editUrl }}" class="text-blue-600 hover:text-blue-900 mr-3" title="Modifier">
            <i class="fas fa-edit"></i>
        </a>

        <form method="POST" action="{{ $deleteUrl }}" class="inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-red-600 hover:text-red-900" title="Supprimer"
                    onclick="return confirm('Supprimer ce produit ?')">
                <i class="fas fa-trash"></i>
            </button>
        </form>
    </td>
</tr>
