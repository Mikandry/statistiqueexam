<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CentreCorrection;
use App\Models\CentreEcrit;
use App\Models\Cisco;
use App\Models\Dren;
use App\Models\GlobalSetting;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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
        $specialTypeExamen = strtoupper((string) request()->query('special_type_examen', $centreTypeForForms));
        if (! in_array($specialTypeExamen, [self::TYPE_BEPC, self::TYPE_CEPE], true)) {
            $specialTypeExamen = self::TYPE_BEPC;
        }
        $specialAnnee = trim((string) request()->query('special_annee', ''));

        $drens = Dren::query()->orderBy('nom')->get();
        $allCiscos = Cisco::query()->with('dren')->orderBy('nom')->get();
        $allCentresCorrection = CentreCorrection::query()->with('cisco.dren')->orderBy('nom')->get();
        $repartitionAnnees = DB::table('repartition_salles')
            ->select('annee')
            ->distinct()
            ->orderByDesc('annee')
            ->pluck('annee');
        if ($specialAnnee === '') {
            $specialAnnee = (string) ($repartitionAnnees->first() ?? '');
        }

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
        $duplicateCentreCorrectionGroups = $this->buildDuplicateCentreCorrectionGroups();
        $duplicateCentreEcritGroups = $this->buildDuplicateCentreEcritGroups();

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
        $centresWithRepartition = DB::table('repartition_salles as rs')
            ->join('centre_ecrits as ce', 'ce.id', '=', 'rs.centre_ecrit_id')
            ->join('centre_corrections as cc', 'cc.id', '=', 'ce.centre_correction_id')
            ->join('ciscos as cs', 'cs.id', '=', 'cc.cisco_id')
            ->join('drens as d', 'd.id', '=', 'cs.dren_id')
            ->select([
                'ce.id',
                'ce.nom as centre_ecrit',
                'cc.nom as centre_correction',
                'cs.nom as cisco',
                'd.nom as dren',
            ])
            ->when($specialAnnee !== '', fn ($query) => $query->where('rs.annee', $specialAnnee))
            ->when($specialTypeExamen === self::TYPE_BEPC, fn ($query) => $query->where('rs.langue', '!=', 'TOTAL'))
            ->when($specialTypeExamen === self::TYPE_CEPE, fn ($query) => $query->where('rs.langue', 'TOTAL'))
            ->where('ce.type_examen', $specialTypeExamen)
            ->groupBy('ce.id', 'ce.nom', 'cc.nom', 'cs.nom', 'd.nom')
            ->orderBy('d.nom')
            ->orderBy('cs.nom')
            ->orderBy('cc.nom')
            ->orderBy('ce.nom')
            ->get();

        $specialCandidatesPage = DB::table('repartition_salles_specifiques as rss')
            ->join('centre_ecrits as ce', 'ce.id', '=', 'rss.centre_ecrit_id')
            ->join('centre_corrections as cc', 'cc.id', '=', 'ce.centre_correction_id')
            ->join('ciscos as cs', 'cs.id', '=', 'cc.cisco_id')
            ->join('drens as d', 'd.id', '=', 'cs.dren_id')
            ->select([
                'rss.id',
                'rss.centre_ecrit_id',
                'rss.annee',
                'rss.type_examen',
                'rss.numero_salle',
                'rss.type_handicap',
                'rss.saisi_par',
                'ce.nom as centre_ecrit',
                'cc.nom as centre_correction',
                'cs.nom as cisco',
                'd.nom as dren',
            ])
            ->when($specialAnnee !== '', fn ($query) => $query->where('rss.annee', $specialAnnee))
            ->when($specialTypeExamen !== '', fn ($query) => $query->where('rss.type_examen', $specialTypeExamen))
            ->orderByDesc('rss.created_at')
            ->orderBy('d.nom')
            ->orderBy('cs.nom')
            ->orderBy('ce.nom')
            ->paginate(15, ['*'], 'page_special_candidates')
            ->withQueryString();

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
            'duplicateCentreCorrectionGroups' => $duplicateCentreCorrectionGroups,
            'duplicateCentreCorrectionRowsCount' => $duplicateCentreCorrectionGroups->sum(fn (array $group) => $group['count']),
            'duplicateCentreEcritGroups' => $duplicateCentreEcritGroups,
            'duplicateCentreEcritRowsCount' => $duplicateCentreEcritGroups->sum(fn (array $group) => $group['count']),
            'selectedDrenId' => $selectedDrenId,
            'selectedCiscoId' => $selectedCiscoId,
            'selectedCentreCorrectionId' => $selectedCentreCorrectionId,
            'selectedTypeExamen' => $selectedTypeExamen,
            'centreTypeForForms' => $centreTypeForForms,
            'dispatchingAxes' => $this->parseConfiguredList((string) ($settings->dispatching_axes ?? '')),
            'dispatchingDropPoints' => $this->parseConfiguredList((string) ($settings->dispatching_drop_points ?? '')),
            'repartitionAnnees' => $repartitionAnnees,
            'specialTypeExamen' => $specialTypeExamen,
            'specialAnnee' => $specialAnnee,
            'centresWithRepartition' => $centresWithRepartition,
            'specialCandidatesPage' => $specialCandidatesPage,
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

    public function updateDuplicateCentreCorrectionName(Request $request, CentreCorrection $centreCorrection): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => [
                'required',
                'string',
                'max:255',
                Rule::unique('centre_corrections', 'nom')
                    ->where(fn ($query) => $query
                        ->where('cisco_id', $centreCorrection->cisco_id)
                        ->where('type_examen', $centreCorrection->type_examen))
                    ->ignore($centreCorrection->id),
            ],
        ]);

        $previousName = $centreCorrection->nom;
        $centreCorrection->update([
            'nom' => trim((string) $validated['nom']),
        ]);

        AuditLog::record($request, 'admin_renommage_doublon_centre_correction', [
            'centre_correction_id' => (int) $centreCorrection->id,
            'ancien_nom' => $previousName,
            'nouveau_nom' => $centreCorrection->nom,
            'type_examen' => $centreCorrection->type_examen,
        ]);

        return redirect()
            ->route('admin.references.index', $request->query())
            ->withFragment('zone-doublons-centres')
            ->with('status', 'Nom du centre de correction modifié.');
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

    public function storeSpecialCandidates(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'annee' => ['required', 'regex:/^\d{4}-\d{4}$/'],
            'type_examen' => ['required', 'in:'.self::TYPE_BEPC.','.self::TYPE_CEPE],
            'centre_ecrit_id' => ['required', 'integer', 'exists:centre_ecrits,id'],
            'candidats_specifiques' => ['required', 'string', 'max:2000'],
        ]);

        $centreEcrit = CentreEcrit::query()
            ->with('centreCorrection.cisco.dren')
            ->findOrFail((int) $validated['centre_ecrit_id']);
        if ($centreEcrit->type_examen !== $validated['type_examen']) {
            return back()->withErrors([
                'centre_ecrit_id' => 'Le centre sélectionné ne correspond pas au type d\'examen.',
            ])->withInput();
        }

        $sallesDisponibles = DB::table('repartition_salles')
            ->where('centre_ecrit_id', $centreEcrit->id)
            ->where('annee', $validated['annee'])
            ->when($validated['type_examen'] === self::TYPE_BEPC, fn ($query) => $query->where('langue', '!=', 'TOTAL'))
            ->when($validated['type_examen'] === self::TYPE_CEPE, fn ($query) => $query->where('langue', 'TOTAL'))
            ->select('numero_salle')
            ->distinct()
            ->orderBy('numero_salle')
            ->pluck('numero_salle')
            ->map(fn ($value) => (int) $value)
            ->all();

        if ($sallesDisponibles === []) {
            return back()->withErrors([
                'centre_ecrit_id' => 'Ce centre n\'a pas encore de répartition par salle pour cette année et ce type.',
            ])->withInput();
        }

        $candidatsSpecifiques = $this->parseSpecialCandidates(
            (string) $validated['candidats_specifiques'],
            $sallesDisponibles
        );

        if ($candidatsSpecifiques === []) {
            return back()->withErrors([
                'candidats_specifiques' => 'Aucune ligne valide. Format attendu: Salle, Type handicap.',
            ])->withInput();
        }

        $nomSaisie = trim((string) ($request->user()?->name ?? 'Admin'));
        $now = now();
        $inserted = 0;

        DB::transaction(function () use ($candidatsSpecifiques, $centreEcrit, $validated, $nomSaisie, $now, &$inserted) {
            foreach ($candidatsSpecifiques as $item) {
                $exists = DB::table('repartition_salles_specifiques')
                    ->where('centre_ecrit_id', $centreEcrit->id)
                    ->where('annee', $validated['annee'])
                    ->where('type_examen', $validated['type_examen'])
                    ->where('numero_salle', $item['numero_salle'])
                    ->where('type_handicap', $item['type_handicap'])
                    ->exists();

                if ($exists) {
                    continue;
                }

                DB::table('repartition_salles_specifiques')->insert([
                    'centre_ecrit_id' => $centreEcrit->id,
                    'annee' => $validated['annee'],
                    'type_examen' => $validated['type_examen'],
                    'numero_salle' => $item['numero_salle'],
                    'type_handicap' => $item['type_handicap'],
                    'saisi_par' => $nomSaisie,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $inserted++;
            }
        });

        AuditLog::record($request, 'admin_ajout_besoins_specifiques', [
            'annee' => $validated['annee'],
            'type_examen' => $validated['type_examen'],
            'centre_ecrit_id' => (int) $centreEcrit->id,
            'centre_ecrit' => $centreEcrit->nom,
            'inserted' => $inserted,
        ]);

        return redirect()
            ->route('admin.references.index', [
                'special_annee' => $validated['annee'],
                'special_type_examen' => $validated['type_examen'],
            ])
            ->withFragment('zone-besoins-specifiques')
            ->with('status', "{$inserted} candidat(s) à besoins spécifiques ajouté(s).");
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

    public function updateDuplicateCentreEcritName(Request $request, CentreEcrit $centreEcrit): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => [
                'required',
                'string',
                'max:255',
                Rule::unique('centre_ecrits', 'nom')
                    ->where(fn ($query) => $query
                        ->where('centre_correction_id', $centreEcrit->centre_correction_id)
                        ->where('type_examen', $centreEcrit->type_examen))
                    ->ignore($centreEcrit->id),
            ],
        ]);

        $previousName = $centreEcrit->nom;
        $centreEcrit->update([
            'nom' => trim((string) $validated['nom']),
        ]);

        AuditLog::record($request, 'admin_renommage_doublon_centre_ecrit', [
            'centre_ecrit_id' => (int) $centreEcrit->id,
            'ancien_nom' => $previousName,
            'nouveau_nom' => $centreEcrit->nom,
            'type_examen' => $centreEcrit->type_examen,
        ]);

        return redirect()
            ->route('admin.references.index', $request->query())
            ->withFragment('zone-doublons-centres')
            ->with('status', 'Nom du centre d\'écrit modifié.');
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

    private function buildDuplicateCentreEcritGroups(): Collection
    {
        return CentreEcrit::query()
            ->with('centreCorrection.cisco.dren')
            ->orderBy('type_examen')
            ->orderBy('nom')
            ->get()
            ->groupBy(fn (CentreEcrit $centre) => ($centre->type_examen ?? '').'|'.$this->normalizeCentreEcritName($centre->nom))
            ->filter(fn (Collection $centres) => $centres->count() > 1)
            ->map(function (Collection $centres) {
                $first = $centres->first();

                return [
                    'nom' => (string) $first->nom,
                    'type_examen' => (string) ($first->type_examen ?? ''),
                    'count' => $centres->count(),
                    'centres' => $centres
                        ->sortBy([
                            fn (CentreEcrit $centre) => $centre->centreCorrection->cisco->dren->nom ?? '',
                            fn (CentreEcrit $centre) => $centre->centreCorrection->cisco->nom ?? '',
                            fn (CentreEcrit $centre) => $centre->centreCorrection->nom ?? '',
                        ])
                        ->values(),
                ];
            })
            ->sortBy([
                fn (array $group) => $group['type_examen'],
                fn (array $group) => $this->normalizeCentreEcritName($group['nom']),
            ])
            ->values();
    }

    private function buildDuplicateCentreCorrectionGroups(): Collection
    {
        return CentreCorrection::query()
            ->with('cisco.dren')
            ->orderBy('type_examen')
            ->orderBy('nom')
            ->get()
            ->groupBy(fn (CentreCorrection $centre) => ($centre->type_examen ?? '').'|'.$this->normalizeCentreName($centre->nom))
            ->filter(fn (Collection $centres) => $centres->count() > 1)
            ->map(function (Collection $centres) {
                $first = $centres->first();

                return [
                    'nom' => (string) $first->nom,
                    'type_examen' => (string) ($first->type_examen ?? ''),
                    'count' => $centres->count(),
                    'centres' => $centres
                        ->sortBy([
                            fn (CentreCorrection $centre) => $centre->cisco->dren->nom ?? '',
                            fn (CentreCorrection $centre) => $centre->cisco->nom ?? '',
                        ])
                        ->values(),
                ];
            })
            ->sortBy([
                fn (array $group) => $group['type_examen'],
                fn (array $group) => $this->normalizeCentreName($group['nom']),
            ])
            ->values();
    }

    private function normalizeCentreEcritName(string $name): string
    {
        return $this->normalizeCentreName($name);
    }

    private function normalizeCentreName(string $name): string
    {
        $normalized = preg_replace('/\s+/', ' ', trim($name)) ?? trim($name);

        return function_exists('mb_strtolower')
            ? mb_strtolower($normalized)
            : strtolower($normalized);
    }

    private function parseSpecialCandidates(string $value, array $sallesDisponibles): array
    {
        if (trim($value) === '') {
            return [];
        }

        $sallesDisponibles = array_flip($sallesDisponibles);

        return collect(preg_split('/\r\n|\r|\n|;/', $value) ?: [])
            ->map(fn (string $line) => trim($line))
            ->filter(fn (string $line) => $line !== '')
            ->map(function (string $line) {
                $delimiter = str_contains($line, ',') ? ',' : (str_contains($line, '-') ? '-' : null);
                if ($delimiter === null) {
                    return null;
                }

                [$salleRaw, $handicapRaw] = array_pad(explode($delimiter, $line, 2), 2, '');
                $salle = (int) trim($salleRaw);
                $handicap = trim($handicapRaw);

                if ($salle <= 0 || $handicap === '') {
                    return null;
                }

                return [
                    'numero_salle' => $salle,
                    'type_handicap' => $handicap,
                ];
            })
            ->filter(fn ($item) => $item !== null && isset($sallesDisponibles[$item['numero_salle']]))
            ->unique(fn (array $item) => $item['numero_salle'].'|'.$item['type_handicap'])
            ->values()
            ->all();
    }
}
