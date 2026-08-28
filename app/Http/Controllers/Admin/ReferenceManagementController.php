<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CentreCorrection;
use App\Models\CentreEcrit;
use App\Models\Cisco;
use App\Models\Dren;
use App\Models\GlobalSetting;
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
        $perPage = 20;
        $selectedDrenId = (int) request()->integer('filter_dren_id', 0);
        $selectedCiscoId = (int) request()->integer('filter_cisco_id', 0);
        $selectedCentreCorrectionId = (int) request()->integer('filter_centre_correction_id', 0);
        $selectedTypeExamen = strtoupper((string) request()->query('filter_type_examen', self::TYPE_BEPC));
        if (! in_array($selectedTypeExamen, ['ALL', self::TYPE_BEPC, self::TYPE_CEPE], true)) {
            $selectedTypeExamen = self::TYPE_BEPC;
        }
        $centreTypeForForms = $selectedTypeExamen === 'ALL' ? self::TYPE_BEPC : $selectedTypeExamen;

        $drens = Dren::query()->orderBy('nom')->get();
        $allCiscos = Cisco::query()->with('dren')->orderBy('nom')->get();
        $allCentresCorrection = CentreCorrection::query()->with('cisco.dren')->orderBy('nom')->get();

        $selectedCisco = $allCiscos->firstWhere('id', $selectedCiscoId);
        if ($selectedCiscoId > 0 && ! $selectedCisco) {
            $selectedCiscoId = 0;
        }
        if ($selectedDrenId > 0 && $selectedCisco && (int) $selectedCisco->dren_id !== $selectedDrenId) {
            $selectedCiscoId = 0;
            $selectedCisco = null;
        }

        $filterCiscos = $allCiscos
            ->filter(fn (Cisco $cisco) => $selectedDrenId <= 0 || (int) $cisco->dren_id === $selectedDrenId)
            ->values();

        $selectedCentreCorrection = $allCentresCorrection->firstWhere('id', $selectedCentreCorrectionId);
        if ($selectedCentreCorrectionId > 0 && ! $selectedCentreCorrection) {
            $selectedCentreCorrectionId = 0;
            $selectedCentreCorrection = null;
        }
        if ($selectedCiscoId > 0 && $selectedCentreCorrection && (int) $selectedCentreCorrection->cisco_id !== $selectedCiscoId) {
            $selectedCentreCorrectionId = 0;
            $selectedCentreCorrection = null;
        }
        if ($selectedDrenId > 0 && $selectedCentreCorrection && (int) ($selectedCentreCorrection->cisco->dren_id ?? 0) !== $selectedDrenId) {
            $selectedCentreCorrectionId = 0;
            $selectedCentreCorrection = null;
        }

        $filterCentresCorrection = $allCentresCorrection
            ->filter(function (CentreCorrection $cc) use ($selectedDrenId, $selectedCiscoId) {
                $matchesDren = $selectedDrenId <= 0 || (int) ($cc->cisco->dren_id ?? 0) === $selectedDrenId;
                $matchesCisco = $selectedCiscoId <= 0 || (int) $cc->cisco_id === $selectedCiscoId;

                return $matchesDren && $matchesCisco;
            })
            ->values();
        if ($selectedTypeExamen !== 'ALL') {
            $filterCentresCorrection = $filterCentresCorrection
                ->filter(fn (CentreCorrection $cc) => $cc->type_examen === $selectedTypeExamen)
                ->values();
        }

        $ciscosQuery = Cisco::query()->with('dren')->orderBy('nom');
        if ($selectedDrenId > 0) {
            $ciscosQuery->where('dren_id', $selectedDrenId);
        }
        if ($selectedCiscoId > 0) {
            $ciscosQuery->whereKey($selectedCiscoId);
        }

        $centresCorrectionQuery = CentreCorrection::query()->with('cisco.dren')->orderBy('nom');
        if ($selectedDrenId > 0) {
            $centresCorrectionQuery->whereHas('cisco', fn ($query) => $query->where('dren_id', $selectedDrenId));
        }
        if ($selectedCiscoId > 0) {
            $centresCorrectionQuery->where('cisco_id', $selectedCiscoId);
        }
        if ($selectedCentreCorrectionId > 0) {
            $centresCorrectionQuery->whereKey($selectedCentreCorrectionId);
        }
        if ($selectedTypeExamen !== 'ALL') {
            $centresCorrectionQuery->where('type_examen', $selectedTypeExamen);
        }

        $centresEcritQuery = CentreEcrit::query()->with('centreCorrection.cisco.dren')->orderBy('nom');
        if ($selectedDrenId > 0) {
            $centresEcritQuery->whereHas('centreCorrection.cisco', fn ($query) => $query->where('dren_id', $selectedDrenId));
        }
        if ($selectedCiscoId > 0) {
            $centresEcritQuery->whereHas('centreCorrection', fn ($query) => $query->where('cisco_id', $selectedCiscoId));
        }
        if ($selectedCentreCorrectionId > 0) {
            $centresEcritQuery->where('centre_correction_id', $selectedCentreCorrectionId);
        }
        if ($selectedTypeExamen !== 'ALL') {
            $centresEcritQuery->where('type_examen', $selectedTypeExamen);
        }

        $formCiscos = $allCiscos
            ->filter(fn (Cisco $cisco) => $selectedDrenId <= 0 || (int) $cisco->dren_id === $selectedDrenId)
            ->values();
        $formCentresCorrection = $allCentresCorrection
            ->filter(function (CentreCorrection $cc) use ($selectedDrenId, $selectedCiscoId, $centreTypeForForms) {
                $matchesDren = $selectedDrenId <= 0 || (int) ($cc->cisco->dren_id ?? 0) === $selectedDrenId;
                $matchesCisco = $selectedCiscoId <= 0 || (int) $cc->cisco_id === $selectedCiscoId;
                $matchesType = $cc->type_examen === $centreTypeForForms;

                return $matchesDren && $matchesCisco && $matchesType;
            })
            ->values();
        $settings = $this->getGlobalSettings();

        return view('admin.references.index', [
            'drens' => $drens,
            'allCiscos' => $allCiscos,
            'allCentresCorrection' => $allCentresCorrection,
            'formCiscos' => $formCiscos,
            'formCentresCorrection' => $formCentresCorrection,
            'filterCiscos' => $filterCiscos,
            'filterCentresCorrection' => $filterCentresCorrection,
            'drensPage' => Dren::query()->orderBy('nom')->paginate($perPage, ['*'], 'page_drens')->withQueryString(),
            'ciscosPage' => $ciscosQuery->paginate($perPage, ['*'], 'page_ciscos')->withQueryString(),
            'centresCorrectionPage' => $centresCorrectionQuery->paginate($perPage, ['*'], 'page_centres_correction')->withQueryString(),
            'centresEcritPage' => $centresEcritQuery->paginate($perPage, ['*'], 'page_centres_ecrit')->withQueryString(),
            'selectedDrenId' => $selectedDrenId,
            'selectedCiscoId' => $selectedCiscoId,
            'selectedCentreCorrectionId' => $selectedCentreCorrectionId,
            'selectedTypeExamen' => $selectedTypeExamen,
            'centreTypeForForms' => $centreTypeForForms,
            'dispatchingAxes' => $this->parseConfiguredList((string) ($settings->dispatching_axes ?? '')),
            'dispatchingDropPoints' => $this->parseConfiguredList((string) ($settings->dispatching_drop_points ?? '')),
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

    public function storeDispatchingAxis(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
        ]);

        $axes = $this->parseConfiguredList((string) ($this->getGlobalSettings()->dispatching_axes ?? ''));
        $axes[] = trim((string) $validated['nom']);

        $this->persistConfiguredList('dispatching_axes', $axes);

        return back()->with('status', 'Axe de dispatching ajouté.');
    }

    public function updateDispatchingAxis(Request $request, int $index): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
        ]);

        $axes = $this->parseConfiguredList((string) ($this->getGlobalSettings()->dispatching_axes ?? ''));
        if (! array_key_exists($index, $axes)) {
            return back()->withErrors(['dispatching_axes' => 'Axe introuvable.']);
        }

        $axes[$index] = trim((string) $validated['nom']);
        $this->persistConfiguredList('dispatching_axes', $axes);

        return back()->with('status', 'Axe de dispatching modifié.');
    }

    public function destroyDispatchingAxis(int $index): RedirectResponse
    {
        $axes = $this->parseConfiguredList((string) ($this->getGlobalSettings()->dispatching_axes ?? ''));
        if (! array_key_exists($index, $axes)) {
            return back()->withErrors(['dispatching_axes' => 'Axe introuvable.']);
        }

        unset($axes[$index]);
        $this->persistConfiguredList('dispatching_axes', $axes);

        return back()->with('status', 'Axe de dispatching supprimé.');
    }

    public function storeDispatchingDropPoint(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
        ]);

        $points = $this->parseConfiguredList((string) ($this->getGlobalSettings()->dispatching_drop_points ?? ''));
        $points[] = trim((string) $validated['nom']);

        $this->persistConfiguredList('dispatching_drop_points', $points);

        return back()->with('status', 'Point de largage ajouté.');
    }

    public function updateDispatchingDropPoint(Request $request, int $index): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
        ]);

        $points = $this->parseConfiguredList((string) ($this->getGlobalSettings()->dispatching_drop_points ?? ''));
        if (! array_key_exists($index, $points)) {
            return back()->withErrors(['dispatching_drop_points' => 'Point de largage introuvable.']);
        }

        $points[$index] = trim((string) $validated['nom']);
        $this->persistConfiguredList('dispatching_drop_points', $points);

        return back()->with('status', 'Point de largage modifié.');
    }

    public function destroyDispatchingDropPoint(int $index): RedirectResponse
    {
        $points = $this->parseConfiguredList((string) ($this->getGlobalSettings()->dispatching_drop_points ?? ''));
        if (! array_key_exists($index, $points)) {
            return back()->withErrors(['dispatching_drop_points' => 'Point de largage introuvable.']);
        }

        unset($points[$index]);
        $this->persistConfiguredList('dispatching_drop_points', $points);

        return back()->with('status', 'Point de largage supprimé.');
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

    private function getGlobalSettings(): GlobalSetting
    {
        $setting = GlobalSetting::query()->first();

        if ($setting) {
            return $setting;
        }

        return GlobalSetting::query()->create([
            'bepc_copy_margin_percent' => 5,
        ]);
    }

    private function parseConfiguredList(string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $value) ?: [])
            ->map(fn ($item) => trim((string) $item))
            ->filter(fn ($item) => $item !== '')
            ->unique()
            ->values()
            ->all();
    }

    private function persistConfiguredList(string $field, array $items): void
    {
        $normalized = collect($items)
            ->map(fn ($item) => trim((string) $item))
            ->filter(fn ($item) => $item !== '')
            ->unique()
            ->values()
            ->implode("\n");

        $this->getGlobalSettings()->update([
            $field => $normalized,
        ]);
    }
}
