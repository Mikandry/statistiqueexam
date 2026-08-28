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
        ]);

        HrDocumentSetting::query()->updateOrCreate(['id' => 1], $data);
        return back()->with('status', 'Paramètres RH mis à jour.');
    }
}