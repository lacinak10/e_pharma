<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $medicines = Medicine::query()
            ->where('is_active', true)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('catalog.index', compact('medicines'));
    }

    public function show(Medicine $catalog)
    {
        // route-model-binding: {catalog} => Medicine
        $medicine = $catalog;

        abort_if(!$medicine->is_active, 404);

        return view('catalog.show', compact('medicine'));
    }
}
