<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\MedicineStoreRequest;
use App\Http\Requests\MedicineUpdateRequest;
use App\Models\Category;
use App\Models\Medicine;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MedicineController extends Controller
{
    public function __construct(private StockService $stockService)
    {
    }

    public function index(Request $request)
    {
        $q        = trim((string) $request->get('q', ''));
        $category = $request->get('category');
        $status   = trim((string) $request->get('status', ''));

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        $medicines = Medicine::query()
            ->with('category')
            ->when($status !== '', fn($query) => $query->where('status', $status))
            ->when($q !== '', fn($query) => $query->where('name', 'like', "%{$q}%"))
            ->when(!empty($category), fn($query) => $query->whereHas('category', fn($q2) => $q2->where('name', $category)))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();


        return view('admin.medicines.index', compact('medicines', 'categories', 'q', 'category', 'status'));
    }

    public function create()
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.medicines.create', compact('categories'));
    }

    public function store(MedicineStoreRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('image_url')) {
            $validated['image_url'] = Storage::disk('public')->put('medicaments', $request->file('image_url'));
        }

        $validated['status'] = $this->stockService->computeStatus(
            (int) $validated['stock'],
            (int) $validated['alert_threshold']
        );

        $medicine = Medicine::create($validated);

        Log::info('Medicine created', ['manager_id' => auth()->id(), 'medicine_id' => $medicine->id, 'name' => $medicine->name]);

        return redirect()->route('manager.medicines.index')->with('success', 'Médicament créé.');
    }

    public function show(Medicine $medicine)
    {
        $medicine->load('category:id,name');

        return view('admin.medicines.show', compact('medicine'));
    }

    public function edit(Medicine $medicine)
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return view('admin.medicines.edit', compact('medicine', 'categories'));
    }

    public function update(MedicineUpdateRequest $request, Medicine $medicine)
    {
        $validated = $request->validated();

        if ($request->hasFile('image_url')) {
            if (!empty($medicine->image_url)) {
                Storage::disk('public')->delete($medicine->image_url);
            }
            $validated['image_url'] = Storage::disk('public')->put('medicaments', $request->file('image_url'));
        }

        $stock     = (int) ($validated['stock'] ?? $medicine->stock);
        $threshold = (int) ($validated['alert_threshold'] ?? $medicine->alert_threshold);

        $validated['status'] = $this->stockService->computeStatus($stock, $threshold);

        $medicine->update($validated);

        Log::info('Medicine updated', ['manager_id' => auth()->id(), 'medicine_id' => $medicine->id, 'name' => $medicine->name]);

        return redirect()->route('manager.medicines.index')->with('success', 'Médicament mis à jour.');
    }

    public function destroy(Medicine $medicine)
    {
        if (!empty($medicine->image_url)) {
            Storage::disk('public')->delete($medicine->image_url);
        }

        Log::info('Medicine deleted', ['manager_id' => auth()->id(), 'medicine_id' => $medicine->id, 'name' => $medicine->name]);

        $medicine->delete();

        return redirect()->route('manager.medicines.index')->with('success', 'Médicament supprimé.');
    }

    public function toggle(Medicine $medicine)
    {
        $medicine->is_active = !$medicine->is_active;
        $medicine->save();

        $msg = $medicine->is_active ? 'Produit activé.' : 'Produit désactivé.';

        return back()->with('success', $msg);
    }
}
