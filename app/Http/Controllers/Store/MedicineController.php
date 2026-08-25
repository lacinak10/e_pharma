<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Medicine;
use Illuminate\Http\Request;

class MedicineController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $category = $request->get('category'); // slug

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id','name','slug']);

        $medicines = Medicine::query()
            ->active()
            ->withAvailabilitySignal()
            ->with('category')
            ->when($q !== '', fn ($query) => $query->where('name', 'like', "%{$q}%"))
            ->when($category, function ($query) use ($category) {
                $query->whereHas('category', fn ($q) => $q->where('slug', $category));
            })
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('store.medicines.index', compact('medicines', 'categories', 'q', 'category'));
    }

    public function show(Medicine $medicine)
    {
        abort_unless($medicine->is_active, 404);

        $medicine->load('category');
        // Recharge la fiche avec les indicateurs de disponibilité constatée.
        $medicine = Medicine::withAvailabilitySignal()->with('category')->findOrFail($medicine->id);

        $related = Medicine::query()
            ->active()
            ->withAvailabilitySignal()
            ->where('category_id', $medicine->category_id)
            ->where('id', '!=', $medicine->id)
            ->limit(4)
            ->get();

        return view('store.medicines.show', compact('medicine', 'related'));
    }
}
