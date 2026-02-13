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
        <div class="text-sm text-gray-900">{{ $medicine->category->name ?? '-' }}</div>
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
        <a href="{{ route('manager.medicines.show',$medicine->id) }}" class="text-primary hover:text-secondary" title="Voir">
    <i class="fa-regular fa-eye"></i>
</a>

<a href="{{ route('manager.medicines.edit',$medicine->id) }}" class="text-gray-700 hover:text-gray-900" title="Modifier">
    <i class="fa-regular fa-pen-to-square"></i>
</a>

<form method="POST" action="{{ route('manager.medicines.toggle',$medicine->id) }}" class="inline">
    @csrf @method('PATCH')
    <button class="{{ $medicine->is_active ? 'text-danger' : 'text-accent' }}" title="{{ $medicine->is_active ? 'Désactiver' : 'Activer' }}">
        <i class="fa-solid {{ $medicine->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
    </button>
</form>

    </td>
</tr>
