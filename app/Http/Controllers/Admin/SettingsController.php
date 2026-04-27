<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function edit(Request $request)
    {
        $settings = $request->session()->get('admin_settings', [
            'language' => 'fr',
            'notifications' => true,
        ]);

        return view('admin.settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'language' => ['required','in:fr,en'],
            'notifications' => ['nullable','boolean'],
        ]);

        $settings = [
            'language' => $validated['language'],
            'notifications' => (bool)($validated['notifications'] ?? false),
        ];

        $request->session()->put('admin_settings', $settings);

        return back()->with('success','Paramètres enregistrés.');
    }
}
