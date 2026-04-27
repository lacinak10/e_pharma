<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $categories = Category::query()
            ->when($q !== '', fn($query) => $query->where('name','like',"%{$q}%"))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.categories.index', compact('categories','q'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required','string','max:255','unique:categories,name'],
            'is_active' => ['nullable','boolean'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = (bool)($validated['is_active'] ?? true);

        Category::create($validated);

        return back()->with('success','Catégorie créée.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => ['required','string','max:255','unique:categories,name,'.$category->id],
            'is_active' => ['nullable','boolean'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = (bool)($validated['is_active'] ?? true);

        $category->update($validated);

        return redirect()->route('manager.categories.index')->with('success','Catégorie mise à jour.');
    }

    public function destroy(Category $category)
    {
        // UX safe: désactive au lieu de delete (évite FK medicines)
        $category->update(['is_active' => false]);

        return back()->with('success','Catégorie désactivée.');
    }
}
