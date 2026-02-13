@extends('layouts.admin')

@section('title','E-PHARMA - Modifier catégorie')
@section('page_title','Modifier catégorie')

@section('content')
<x-admin.card title="Modifier la catégorie">
    <form method="POST" action="{{ route('manager.categories.update',$category) }}" class="space-y-4">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                <x-admin.input name="name" value="{{ old('name',$category->name) }}" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Slug (auto)</label>
                <x-admin.input value="{{ $category->slug }}" disabled />
            </div>
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" {{ $category->is_active ? 'checked' : '' }}
                   class="h-4 w-4 border-gray-300 rounded">
            <span class="text-sm text-gray-700">Active</span>
        </div>

        <div class="pt-4 border-t flex justify-end gap-2">
            <a href="{{ route('manager.categories.index') }}" class="px-4 py-2 rounded-lg border bg-white text-gray-700 hover:bg-gray-50">Retour</a>
            <x-admin.button type="submit" variant="primary">Mettre à jour</x-admin.button>
        </div>
    </form>
</x-admin.card>
@endsection
