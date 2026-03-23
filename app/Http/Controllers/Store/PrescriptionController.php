<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    public function create()
    {
        return view('store.prescriptions.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'prescription' => ['required','file','mimes:jpg,jpeg,png,pdf','max:4096'],
        ]);

        $path = $request->file('prescription')->store('prescriptions', 'local');

        return back()->with('success', "Ordonnance envoyée avec succès.")->with('prescription_path', $path);
    }
}
