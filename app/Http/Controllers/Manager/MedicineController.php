<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\MedicineStoreRequest;
use App\Http\Requests\MedicineUpdateRequest;
use App\Models\Medicine;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\Category;

class MedicineController extends Controller
{

    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

$category = $request->get('category'); // slug (string|null)
$statusRaw = $request->get('status');  // string|enum|null selon ton form

// Normalise status en string "propre"
$status = $statusRaw instanceof \BackedEnum
    ? $statusRaw->value
    : ($statusRaw instanceof \UnitEnum ? $statusRaw->name : (is_string($statusRaw) ? trim($statusRaw) : ''));

$categories = Category::query()
    ->where('is_active', true)
    ->orderBy('name')
    ->get(['id','name','slug']);

$medicines = Medicine::query()
    ->where('is_active', true)
    ->with('category')
    ->when($status !== '', fn ($query) => $query->where('status', $status))
    ->when($q !== '', fn ($query) => $query->where('name', 'like', "%{$q}%"))
    ->when(!empty($category), function ($query) use ($category) {
        $query->whereHas('category', fn ($q) => $q->where('name', $category));
    })
    ->orderBy('name')
    ->paginate(12)
    ->withQueryString();


        return view('admin.medicines.index',  compact('medicines', 'categories', 'q', 'category', 'status'));
    }

    // public function create()
    // {
    //     return view('admin.medicines.create');
    // }

    public function store(MedicineStoreRequest $request)
    {

        $validated = $request->validated();

        if($request->hasFile('image_url')){
            $validated['image_url'] = Storage::disk('public')->put("medicaments",$request->image_url);
        }

        if($validated['stock'] >= $validated['alert_threshold']){
            $validated['status'] = 'En stock';
        }else if ($validated['stock']== 0){
            $validated['status'] = 'Épuisé';

        }else{
        $validated['status'] = 'Stock faible';

        }

        Medicine::create($validated);

        return redirect()
            ->route('manager.medicines.index')
            ->with('success', 'Médicament créé.');
    }

    public function edit(Medicine $medicine)
{
    $categories = Category::query()
        ->where('is_active', true)
        ->orderBy('name')
        ->get(['id','name','slug']);

    return view('admin.medicines.edit', compact('medicine','categories'));
}

public function update(MedicineUpdateRequest $request, Medicine $medicine)
{
    $validated = $request->validated();

    // Upload nouvelle image (si fournie)
    if ($request->hasFile('image_url')) {
        // Optionnel: supprimer l'ancienne image si elle existe
        if (!empty($medicine->image_url)) {
            Storage::disk('public')->delete($medicine->image_url);
        }

        $validated['image_url'] = Storage::disk('public')->put("medicaments", $request->file('image_url'));
    }

    // Recalcul statut stock (même logique que store)
    $stock = (int) ($validated['stock'] ?? $medicine->stock);
    $threshold = (int) ($validated['alert_threshold'] ?? $medicine->alert_threshold);

    if ($stock <= 0) {
        $validated['status'] = 'Épuisé';
    } elseif ($stock <= $threshold) {
        $validated['status'] = 'Stock faible';
    } else {
        $validated['status'] = 'En stock';
    }

    $medicine->update($validated);

    return redirect()
        ->route('manager.medicines.index')
        ->with('success', 'Médicament mis à jour.');
}

    public function destroy(Medicine $medicine)
    {
        $medicine->delete();

        return redirect()
            ->route('manager.medicines.index')
            ->with('success', 'Médicament supprimé.');
    }


public function show(Medicine $medicine)
{
    $medicine->load('category:id,name');
    return view('admin.medicines.show', compact('medicine'));
}

public function toggle(Medicine $medicine)
{
    $medicine->is_active = !$medicine->is_active;
    $medicine->save();

    return back()->with('success', $medicine->is_active ? 'Produit activé.' : 'Produit désactivé.');
}

}


