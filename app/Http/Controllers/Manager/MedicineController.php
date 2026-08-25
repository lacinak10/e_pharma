<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\MedicineStoreRequest;
use App\Http\Requests\MedicineUpdateRequest;
use App\Models\Category;
use App\Models\Medicine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Référentiel des médicaments.
 *
 * ePharma ne détient aucun stock : ce catalogue décrit ce que l'on sait
 * commander, pas ce que l'on possède. La disponibilité réelle est établie
 * commande par commande, en appelant les pharmacies partenaires.
 */
class MedicineController extends Controller
{
    public function index(Request $request): View
    {
        $q        = $request->string('q')->toString();
        $category = $request->string('category')->toString();

        $medicines = Medicine::query()
            ->withAvailabilitySignal()
            ->with('category')
            ->when($q !== '', fn ($query) => $query->where('name', 'like', "%{$q}%"))
            ->when($category !== '', fn ($query) => $query->whereHas('category', fn ($c) => $c->where('slug', $category)))
            ->when($request->boolean('rx'), fn ($query) => $query->where('requires_prescription', true))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.medicines.index', [
            'medicines'  => $medicines,
            'categories' => Category::where('is_active', true)->orderBy('name')->get(['id', 'name', 'slug']),
            'q'          => $q,
            'category'   => $category,
        ]);
    }

    public function create(): View
    {
        return view('admin.medicines.form', [
            'medicine'   => new Medicine(['is_active' => true]),
            'categories' => Category::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(MedicineStoreRequest $request): RedirectResponse
    {
        Medicine::create($this->payload($request));

        return redirect()->route('manager.medicines.index')->with('success', 'Médicament ajouté au référentiel.');
    }

    public function show(Medicine $medicine): RedirectResponse
    {
        return redirect()->route('manager.medicines.edit', $medicine);
    }

    public function edit(Medicine $medicine): View
    {
        return view('admin.medicines.form', [
            'medicine'   => $medicine,
            'categories' => Category::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(MedicineUpdateRequest $request, Medicine $medicine): RedirectResponse
    {
        $medicine->update($this->payload($request, $medicine));

        return redirect()->route('manager.medicines.index')->with('success', 'Médicament mis à jour.');
    }

    public function destroy(Medicine $medicine): RedirectResponse
    {
        $medicine->update(['is_active' => false]);

        return back()->with('success', 'Médicament retiré du catalogue.');
    }

    public function toggle(Medicine $medicine): RedirectResponse
    {
        $medicine->update(['is_active' => ! $medicine->is_active]);

        return back()->with('success', $medicine->is_active ? 'Médicament réactivé.' : 'Médicament retiré du catalogue.');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Request $request, ?Medicine $medicine = null): array
    {
        $data = $request->validated();

        $data['is_active']             = $request->boolean('is_active');
        $data['requires_prescription'] = $request->boolean('requires_prescription');
        $data['reference'] ??= $medicine?->reference ?? 'MED-' . Str::upper(Str::random(6));

        if ($request->hasFile('image')) {
            $data['image_url'] = $request->file('image')->store('medicines', 'public');
        }

        if ($request->boolean('remove_image') && $medicine?->image_url) {
            Storage::disk('public')->delete($medicine->image_url);
            $data['image_url'] = null;
        }

        return $data;
    }
}
