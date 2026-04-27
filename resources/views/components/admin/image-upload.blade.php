@props(['name' => 'image'])

<div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md"
     style="background-image: url(&quot;data:image/svg+xml,%3csvg width='100%25' height='100%25' xmlns='http://www.w3.org/2000/svg'%3e%3crect width='100%25' height='100%25' fill='none' stroke='%23D1D5DB' stroke-width='2' stroke-dasharray='6%2c 6' stroke-dashoffset='0' stroke-linecap='square'/%3e%3c/svg%3e&quot;);">
    <div class="space-y-1 text-center">
        <div class="flex text-sm text-gray-600 justify-center">
            <label for="{{ $name }}" class="relative cursor-pointer bg-white rounded-md font-medium text-primary hover:text-secondary focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary">
                <span>Télécharger une image</span>
                <input id="{{ $name }}" name="{{ $name }}" type="file" class="sr-only" accept="image/*">
            </label>
            <p class="pl-1">ou glisser-déposer</p>
        </div>
        <p class="text-xs text-gray-500">
            PNG, JPG, GIF jusqu'à 2MB
        </p>
    </div>
</div>
