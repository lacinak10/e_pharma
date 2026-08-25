<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PharmacyController extends Controller
{
    public function index(Request $request): View
    {
        $pharmacies = Pharmacy::query()
            ->when($request->string('q')->toString(), fn ($query, $term) => $query->where(
                fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('area', 'like', "%{$term}%")
            ))
            ->orderByDesc('is_active')
            ->orderByDesc('reliability')
            ->paginate(15)
            ->withQueryString();

        return view('admin.pharmacies.index', compact('pharmacies'));
    }

    public function create(): View
    {
        return view('admin.pharmacies.form', ['pharmacy' => new Pharmacy(['reliability' => 100, 'avg_response_minutes' => 3])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Pharmacy::create($this->validated($request));

        return redirect()->route('manager.pharmacies.index')->with('success', 'Pharmacie partenaire ajoutée.');
    }

    public function edit(Pharmacy $pharmacy): View
    {
        return view('admin.pharmacies.form', compact('pharmacy'));
    }

    public function update(Request $request, Pharmacy $pharmacy): RedirectResponse
    {
        $pharmacy->update($this->validated($request));

        return redirect()->route('manager.pharmacies.index')->with('success', 'Pharmacie mise à jour.');
    }

    public function destroy(Pharmacy $pharmacy): RedirectResponse
    {
        $pharmacy->update(['is_active' => false]);

        return back()->with('success', 'Pharmacie désactivée.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name'                 => ['required', 'string', 'max:120'],
            'area'                 => ['required', 'string', 'max:80'],
            'phone'                => ['required', 'string', 'max:30'],
            'address'              => ['nullable', 'string', 'max:200'],
            'latitude'             => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'            => ['nullable', 'numeric', 'between:-180,180'],
            'is_24h'               => ['boolean'],
            'reliability'          => ['required', 'integer', 'between:0,100'],
            'avg_response_minutes' => ['required', 'integer', 'between:1,60'],
            'is_active'            => ['boolean'],
        ]);
    }
}
