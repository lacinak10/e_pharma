<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::query()
            ->orderBy('name')
            ->paginate(20);

        return view('manager.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('manager.categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $slugBase = Str::slug($validated['name']);
        $slug = $this->uniqueSlug($slugBase);

        $category = Category::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'is_active' => (bool)($validated['is_active'] ?? true),
        ]);

        return redirect()
            ->route('manager.categories.index')
            ->with('success', 'Catégorie créée avec succès.');
    }

    public function show(Category $category)
    {
        return view('manager.categories.show', compact('category'));
    }

    public function edit(Category $category)
    {
        return view('manager.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('categories', 'name')->ignore($category->id),
            ],
            'is_active' => ['nullable', 'boolean'],
        ]);

        // Si le nom change, on régénère le slug
        if ($validated['name'] !== $category->name) {
            $slugBase = Str::slug($validated['name']);
            $category->slug = $this->uniqueSlug($slugBase, $category->id);
        }

        $category->name = $validated['name'];
        $category->is_active = (bool)($validated['is_active'] ?? $category->is_active);
        $category->save();

        return redirect()
            ->route('manager.categories.index')
            ->with('success', 'Catégorie mise à jour avec succès.');
    }

    public function destroy(Category $category)
    {
        // Sécurité: éviter de supprimer une catégorie utilisée par des medicines
        if (method_exists($category, 'medicines') && $category->medicines()->exists()) {
            return back()->with('error', 'Impossible de supprimer : cette catégorie est utilisée par des médicaments.');
        }

        $category->delete();

        return redirect()
            ->route('manager.categories.index')
            ->with('success', 'Catégorie supprimée avec succès.');
    }

    /**
     * Génère un slug unique.
     * $ignoreId sert à ignorer l’enregistrement en cours lors d’un update.
     */
    private function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = $base ?: 'categorie';
        $i = 2;

        while (
            Category::query()
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }
}
