<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HrDocumentSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HrSettingsController extends Controller
{
    public function index()
    {
        return view('admin.hr.settings', ['settings' => HrDocumentSetting::query()->first()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ministere' => ['nullable', 'string', 'max:255'],
            'secretariat_general' => ['nullable', 'string', 'max:255'],
            'direction_generale' => ['nullable', 'string', 'max:255'],
            'direction' => ['nullable', 'string', 'max:255'],
            'service' => ['nullable', 'string', 'max:255'],
            'reference_prefix' => ['required', 'string', 'max:30'],
            'next_reference_number' => ['required', 'integer', 'min:1', 'max:999999'],
            'reference_year' => ['nullable', 'integer', 'min:2000', 'max:2200'],
            'signataire' => ['nullable', 'string', 'max:255'],
            'signataire_qualite' => ['nullable', 'string', 'max:255'],
        ]);

        $settings = HrDocumentSetting::query()->updateOrCreate(['id' => 1], $data);

        // Configuration des champs visibles par type de document
        if ($request->has('fields_config')) {
            $raw = (array) $request->input('fields_config');
            $fieldsConfig = [];

            foreach ($raw as $document => $fields) {
                $fieldsConfig[$document] = array_values(array_filter(
                    (array) $fields,
                    fn ($f) => in_array($f, array_keys(HrDocumentSetting::availableFields()), true)
                ));
            }

            $settings->update(['fields_config' => $fieldsConfig]);
        }

        return back()->with('status', 'Paramètres RH mis à jour.');
    }
}
