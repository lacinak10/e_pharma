<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\MedicineStoreRequest;
use App\Http\Requests\MedicineUpdateRequest;
use App\Models\Medicine;
use Illuminate\Http\Request;

class MedicineController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Medicine::class, 'medicine');
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $medicines = Medicine::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('manager.medicines.index', compact('medicines', 'q'));
    }

    public function create()
    {
        return view('manager.medicines.create');
    }

    public function store(MedicineStoreRequest $request)
    {
        Medicine::create($request->validated());

        return redirect()
            ->route('manager.medicines.index')
            ->with('success', 'Médicament créé.');
    }

    public function edit(Medicine $medicine)
    {
        return view('manager.medicines.edit', compact('medicine'));
    }

    public function update(MedicineUpdateRequest $request, Medicine $medicine)
    {
        $medicine->update($request->validated());

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
}
