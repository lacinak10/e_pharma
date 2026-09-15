<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\CompanyProfile;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct(private CompanyProfile $company)
    {
    }

    public function edit(Request $request)
    {
        $settings = $request->session()->get('admin_settings', [
            'language' => 'fr',
            'notifications' => true,
        ]);

        return view('admin.settings.edit', [
            'settings' => $settings,
            'company'  => $this->company->current(),
        ]);
    }

    public function update(Request $request)
    {
        /*
         * Cet écran est partagé avec le livreur, mais les deux blocs n'ont pas
         * la même portée : les préférences ne valent que pour la session de
         * celui qui les règle, alors que les coordonnées s'affichent à tous les
         * clients. Seul le manager rédige donc le contact public.
         */
        $isManager = $request->user()->isManager();

        $rules = [
            'language' => ['required','in:fr,en'],
            'notifications' => ['nullable','boolean'],
        ];

        if ($isManager) {
            $rules += CompanyProfile::rules();
        }

        $validated = $request->validate($rules);

        if ($isManager) {
            $this->company->save($validated);
        }

        $request->session()->put('admin_settings', [
            'language' => $validated['language'],
            'notifications' => (bool)($validated['notifications'] ?? false),
        ]);

        return back()->with('success','Paramètres enregistrés.');
    }
}
