<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Services\StockService;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function __construct(private StockService $stockService)
    {
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $medicines = Medicine::query()
            ->where('is_active', true)
            ->with('category:id,name')
            ->when($q !== '', fn($query) => $query->where('name', 'like', "%{$q}%"))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.stock.index', compact('medicines', 'q'));
    }

    public function edit(Medicine $medicine)
    {
        return view('admin.stock.edit', compact('medicine'));
    }

    public function update(Request $request, Medicine $medicine)
    {
        $validated = $request->validate([
            'stock'           => ['required', 'integer', 'min:0'],
            'alert_threshold' => ['required', 'integer', 'min:0'],
        ]);

        $medicine->stock           = (int) $validated['stock'];
        $medicine->alert_threshold = (int) $validated['alert_threshold'];
        $medicine->status          = $this->stockService->computeStatus($medicine->stock, $medicine->alert_threshold);
        $medicine->save();

        return redirect()->route('manager.stock.index')->with('success', 'Stock mis à jour.');
    }
}
