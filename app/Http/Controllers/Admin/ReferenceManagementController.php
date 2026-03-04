<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CentreCorrection;
use App\Models\CentreEcrit;
use App\Models\Cisco;
use App\Models\Dren;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReferenceManagementController extends Controller
{
    private const TYPE_BEPC = 'BEPC';

    private const TYPE_CEPE = 'CEPE';

    public function index(): View
    {
        return view('admin.references.index', [
            'drens' => Dren::query()->orderBy('nom')->get(),
            'ciscos' => Cisco::query()->with('dren')->orderBy('nom')->get(),
            'centresCorrection' => CentreCorrection::query()->with('cisco.dren')->orderBy('nom')->get(),
            'centresEcrit' => CentreEcrit::query()->with('centreCorrection.cisco.dren')->orderBy('nom')->get(),
        ]);
    }

    public function storeDren(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255', 'unique:drens,nom'],
        ]);

        Dren::query()->create([
            'nom' => trim((string) $validated['nom']),
        ]);

        return back()->with('status', 'DREN ajouté.');
    }

    public function updateDren(Request $request, Dren $dren): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255', Rule::unique('drens', 'nom')->ignore($dren->id)],
        ]);

        $dren->update([
            'nom' => trim((string) $validated['nom']),
        ]);

        return back()->with('status', 'DREN modifié.');
    }

    public function destroyDren(Dren $dren): RedirectResponse
    {
        $drenName = $dren->nom;
        $dren->delete();

        return back()->with('status', "DREN {$drenName} supprimé(e) avec ses CISCO et centres associés.");
    }

    public function storeCisco(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'dren_id' => ['required', 'integer', 'exists:drens,id'],
            'nom' => ['required', 'string', 'max:255'],
        ]);

        Cisco::query()->create([
            'dren_id' => (int) $validated['dren_id'],
            'nom' => trim((string) $validated['nom']),
        ]);

        return back()->with('status', 'CISCO ajouté.');
    }

    public function updateCisco(Request $request, Cisco $cisco): RedirectResponse
    {
        $validated = $request->validate([
            'dren_id' => ['required', 'integer', 'exists:drens,id'],
            'nom' => ['required', 'string', 'max:255'],
        ]);

        $cisco->update([
            'dren_id' => (int) $validated['dren_id'],
            'nom' => trim((string) $validated['nom']),
        ]);

        return back()->with('status', 'CISCO modifié.');
    }

    public function destroyCisco(Cisco $cisco): RedirectResponse
    {
        $ciscoName = $cisco->nom;
        $cisco->delete();

        return back()->with('status', "CISCO {$ciscoName} supprimé(e) avec ses centres associés.");
    }

    public function storeCentreCorrection(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cisco_id' => ['required', 'integer', 'exists:ciscos,id'],
            'nom' => ['required', 'string', 'max:255'],
            'type_examen' => ['required', 'in:'.self::TYPE_BEPC.','.self::TYPE_CEPE],
        ]);

        CentreCorrection::query()->create([
            'cisco_id' => (int) $validated['cisco_id'],
            'nom' => trim((string) $validated['nom']),
            'type_examen' => $validated['type_examen'],
        ]);

        return back()->with('status', 'Centre de correction ajouté.');
    }

    public function updateCentreCorrection(Request $request, CentreCorrection $centreCorrection): RedirectResponse
    {
        $validated = $request->validate([
            'cisco_id' => ['required', 'integer', 'exists:ciscos,id'],
            'nom' => ['required', 'string', 'max:255'],
            'type_examen' => ['required', 'in:'.self::TYPE_BEPC.','.self::TYPE_CEPE],
        ]);

        $centreCorrection->update([
            'cisco_id' => (int) $validated['cisco_id'],
            'nom' => trim((string) $validated['nom']),
            'type_examen' => $validated['type_examen'],
        ]);

        return back()->with('status', 'Centre de correction modifié.');
    }

    public function destroyCentreCorrection(CentreCorrection $centreCorrection): RedirectResponse
    {
        $centreName = $centreCorrection->nom;
        $typeExamen = $centreCorrection->type_examen;
        $centreCorrection->delete();

        return back()->with('status', "Centre de correction {$centreName} ({$typeExamen}) supprimé avec ses centres d'écrit et statistiques associées.");
    }

    public function storeCentreEcrit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'centre_correction_id' => ['required', 'integer', 'exists:centre_corrections,id'],
            'nom' => ['required', 'string', 'max:255'],
            'type_examen' => ['required', 'in:'.self::TYPE_BEPC.','.self::TYPE_CEPE],
        ]);

        $centreCorrection = CentreCorrection::query()->findOrFail((int) $validated['centre_correction_id']);
        if ($centreCorrection->type_examen !== $validated['type_examen']) {
            return back()->withErrors([
                'type_examen' => 'Le type du centre d\'écrit doit correspondre au type du centre de correction.',
            ]);
        }

        CentreEcrit::query()->create([
            'centre_correction_id' => (int) $validated['centre_correction_id'],
            'nom' => trim((string) $validated['nom']),
            'type_examen' => $validated['type_examen'],
        ]);

        return back()->with('status', 'Centre d\'écrit ajouté.');
    }

    public function updateCentreEcrit(Request $request, CentreEcrit $centreEcrit): RedirectResponse
    {
        $validated = $request->validate([
            'centre_correction_id' => ['required', 'integer', 'exists:centre_corrections,id'],
            'nom' => ['required', 'string', 'max:255'],
            'type_examen' => ['required', 'in:'.self::TYPE_BEPC.','.self::TYPE_CEPE],
        ]);

        $centreCorrection = CentreCorrection::query()->findOrFail((int) $validated['centre_correction_id']);
        if ($centreCorrection->type_examen !== $validated['type_examen']) {
            return back()->withErrors([
                'type_examen' => 'Le type du centre d\'écrit doit correspondre au type du centre de correction.',
            ]);
        }

        $centreEcrit->update([
            'centre_correction_id' => (int) $validated['centre_correction_id'],
            'nom' => trim((string) $validated['nom']),
            'type_examen' => $validated['type_examen'],
        ]);

        return back()->with('status', 'Centre d\'écrit modifié.');
    }

    public function destroyCentreEcrit(CentreEcrit $centreEcrit): RedirectResponse
    {
        $centreName = $centreEcrit->nom;
        $typeExamen = $centreEcrit->type_examen;
        $centreEcrit->delete();

        return back()->with('status', "Centre d'écrit {$centreName} ({$typeExamen}) supprimé avec ses statistiques associées.");
    }
}
