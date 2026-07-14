<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CentreCorrection;
use App\Models\CentreEcrit;
use App\Models\Cisco;
use App\Models\Dren;
use App\Models\GlobalSetting;
use App\Models\RepartitionSalle;
use Illuminate\Support\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StatisticController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'annee' => (string) $request->query('annee', ''),
            'type_examen' => strtoupper((string) $request->query('type_examen', 'ALL')),
            'dren_id' => (int) $request->integer('dren_id', 0),
            'cisco_id' => (int) $request->integer('cisco_id', 0),
            'centre_correction_id' => (int) $request->integer('centre_correction_id', 0),
            'centre_ecrit_id' => (int) $request->integer('centre_ecrit_id', 0),
            'centre_search' => trim((string) $request->query('centre_search', '')),
        ];

        if (! in_array($filters['type_examen'], ['ALL', 'BEPC', 'CEPE'], true)) {
            $filters['type_examen'] = 'ALL';
        }

        $statsQuery = RepartitionSalle::query()
            ->with('centreEcrit.centreCorrection.cisco.dren')
            ->orderByDesc('annee')
            ->orderBy('centre_ecrit_id')
            ->orderBy('langue')
            ->orderBy('numero_salle');

        if ($filters['annee'] !== '') {
            $statsQuery->where('annee', $filters['annee']);
        }
        if ($filters['type_examen'] === 'BEPC') {
            $statsQuery->where('langue', '!=', 'TOTAL');
        } elseif ($filters['type_examen'] === 'CEPE') {
            $statsQuery->where('langue', 'TOTAL');
        }
        if ($filters['dren_id'] > 0) {
            $statsQuery->whereHas('centreEcrit.centreCorrection.cisco', fn ($query) => $query->where('dren_id', $filters['dren_id']));
        }
        if ($filters['cisco_id'] > 0) {
            $statsQuery->whereHas('centreEcrit.centreCorrection', fn ($query) => $query->where('cisco_id', $filters['cisco_id']));
        }
        if ($filters['centre_correction_id'] > 0) {
            $statsQuery->whereHas('centreEcrit', fn ($query) => $query->where('centre_correction_id', $filters['centre_correction_id']));
        }
        if ($filters['centre_ecrit_id'] > 0) {
            $statsQuery->where('centre_ecrit_id', $filters['centre_ecrit_id']);
        }
        if ($filters['centre_search'] !== '') {
            $needle = $filters['centre_search'];
            $statsQuery->whereHas('centreEcrit', fn ($query) => $query->where('nom', 'like', "%{$needle}%"));
        }

        $bulkCentreEcritId = $filters['centre_ecrit_id'];
        if ($bulkCentreEcritId <= 0) {
            $matchingCentreIds = (clone $statsQuery)
                ->reorder()
                ->select('centre_ecrit_id')
                ->distinct()
                ->limit(2)
                ->pluck('centre_ecrit_id');

            if ($matchingCentreIds->count() === 1) {
                $bulkCentreEcritId = (int) $matchingCentreIds->first();
            }
        }

        $stats = $statsQuery->paginate(30)->withQueryString();
        $annees = RepartitionSalle::query()
            ->select('annee')
            ->distinct()
            ->orderByDesc('annee')
            ->pluck('annee');
        $drens = Dren::query()->orderBy('nom')->get(['id', 'nom']);
        $ciscos = Cisco::query()
            ->with('dren')
            ->orderBy('nom')
            ->get(['id', 'dren_id', 'nom']);
        $centresCorrection = CentreCorrection::query()
            ->with('cisco.dren')
            ->when($filters['type_examen'] !== 'ALL', fn ($query) => $query->where('type_examen', $filters['type_examen']))
            ->orderBy('nom')
            ->get(['id', 'cisco_id', 'nom', 'type_examen']);
        $centresEcrit = CentreEcrit::query()
            ->with('centreCorrection.cisco.dren')
            ->when($filters['type_examen'] !== 'ALL', fn ($query) => $query->where('type_examen', $filters['type_examen']))
            ->orderBy('nom')
            ->get(['id', 'centre_correction_id', 'nom', 'type_examen']);
        $bulkStats = $bulkCentreEcritId > 0
            ? $this->buildBulkStatsForCentre($bulkCentreEcritId, $filters)
            : collect();
        $globalSetting = GlobalSetting::query()->first();
        $dispatchingRows = $this->buildDispatchingRows($filters);

        return view('admin.statistics.index', [
            'stats' => $stats,
            'filters' => $filters,
            'annees' => $annees,
            'drens' => $drens,
            'ciscos' => $ciscos,
            'centresCorrection' => $centresCorrection,
            'centresEcrit' => $centresEcrit,
            'bulkStats' => $bulkStats,
            'bulkCentreEcritId' => $bulkCentreEcritId,
            'globalSetting' => $globalSetting,
            'dispatchingRows' => $dispatchingRows,
            'dispatchingAxes' => $this->parseConfiguredList((string) ($globalSetting?->dispatching_axes ?? '')),
            'dispatchingDropPoints' => $this->parseConfiguredList((string) ($globalSetting?->dispatching_drop_points ?? '')),
            'allExistingDispatchingAxes' => $this->getExistingDispatchingValues('axe_dispatching'),
            'allExistingDispatchingDropPoints' => $this->getExistingDispatchingValues('point_largage'),
        ]);
    }

    public function updateGeneralSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bepc_copy_margin_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'subject_soubique_ge_capacity' => ['required', 'integer', 'min:1', 'max:100'],
            'subject_soubique_subject_capacity' => ['required', 'integer', 'min:1', 'max:100'],
            'sord_sheet_page_capacity' => ['required', 'integer', 'min:1', 'max:200'],
            'cepe_pages_francais' => ['required', 'integer', 'min:0', 'max:50'],
            'cepe_pages_connaissances_usuelles' => ['required', 'integer', 'min:0', 'max:50'],
            'cepe_pages_geographie' => ['required', 'integer', 'min:0', 'max:50'],
            'cepe_pages_malagasy' => ['required', 'integer', 'min:0', 'max:50'],
            'cepe_pages_operation' => ['required', 'integer', 'min:0', 'max:50'],
            'cepe_pages_probleme' => ['required', 'integer', 'min:0', 'max:50'],
            'cepe_pages_tffmom' => ['required', 'integer', 'min:0', 'max:50'],
            'bepc_pages_malagasy' => ['required', 'integer', 'min:0', 'max:50'],
            'bepc_pages_svt' => ['required', 'integer', 'min:0', 'max:50'],
            'bepc_pages_francais' => ['required', 'integer', 'min:0', 'max:50'],
            'bepc_pages_anglais' => ['required', 'integer', 'min:0', 'max:50'],
            'bepc_pages_esp' => ['required', 'integer', 'min:0', 'max:50'],
            'bepc_pages_pc' => ['required', 'integer', 'min:0', 'max:50'],
            'bepc_pages_math' => ['required', 'integer', 'min:0', 'max:50'],
            'bepc_pages_hg' => ['required', 'integer', 'min:0', 'max:50'],
            'bepc_pages_all' => ['required', 'integer', 'min:0', 'max:50'],
            'bepc_print_order' => ['nullable', 'string', 'max:1000'],
            'cepe_print_order' => ['nullable', 'string', 'max:1000'],
            'dispatching_axes' => ['nullable', 'string', 'max:5000'],
            'dispatching_drop_points' => ['nullable', 'string', 'max:5000'],
        ]);

        $setting = GlobalSetting::query()->first();

        if (! $setting) {
            GlobalSetting::query()->create($validated);
        } else {
            $setting->update($validated);
        }

        return back()->with('status', 'Paramètres généraux mis à jour.');
    }

    public function update(Request $request, RepartitionSalle $stat): RedirectResponse
    {
        $validated = $request->validate([
            'effectif' => ['required', 'integer', 'min:0', 'max:1000'],
        ]);

        $stat->update($validated);

        return back()->with('status', 'Effectif modifié. Salle/année/langue verrouillées.');
    }

    public function updateCentreDispatching(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'centre_ecrit_id' => ['required', 'integer', 'exists:centre_ecrits,id'],
            'annee' => ['required', 'regex:/^\d{4}-\d{4}$/'],
            'type_examen' => ['required', 'in:BEPC,CEPE'],
            'axe_dispatching' => ['required', 'string', 'max:255'],
            'point_largage' => ['nullable', 'string', 'max:255'],
            'filter_annee' => ['nullable', 'string', 'max:9'],
            'filter_type_examen' => ['nullable', 'in:ALL,BEPC,CEPE'],
            'filter_dren_id' => ['nullable', 'integer', 'min:0'],
            'filter_cisco_id' => ['nullable', 'integer', 'min:0'],
            'filter_centre_correction_id' => ['nullable', 'integer', 'min:0'],
            'filter_centre_ecrit_id' => ['nullable', 'integer', 'min:0'],
            'filter_centre_search' => ['nullable', 'string', 'max:255'],
        ]);

        $centreEcritId = (int) $validated['centre_ecrit_id'];
        $typeExamen = (string) $validated['type_examen'];
        $axeDispatching = trim((string) $validated['axe_dispatching']);
        $pointLargage = trim((string) ($validated['point_largage'] ?? ''));

        $query = RepartitionSalle::query()
            ->where('centre_ecrit_id', $centreEcritId)
            ->where('annee', $validated['annee'])
            ->when($typeExamen === 'BEPC', fn ($query) => $query->where('langue', '!=', 'TOTAL'))
            ->when($typeExamen === 'CEPE', fn ($query) => $query->where('langue', 'TOTAL'));

        $updated = $query->update([
            'axe_dispatching' => $axeDispatching,
            'point_largage' => $pointLargage,
        ]);

        if ($updated === 0) {
            return back()->withErrors(['dispatching' => 'Aucune ligne saisie trouvée pour ce centre, cette année et ce type.']);
        }

        $returnFilters = [
            'annee' => (string) ($validated['filter_annee'] ?? ''),
            'type_examen' => strtoupper((string) ($validated['filter_type_examen'] ?? 'ALL')),
            'dren_id' => (int) ($validated['filter_dren_id'] ?? 0),
            'cisco_id' => (int) ($validated['filter_cisco_id'] ?? 0),
            'centre_correction_id' => (int) ($validated['filter_centre_correction_id'] ?? 0),
            'centre_ecrit_id' => (int) ($validated['filter_centre_ecrit_id'] ?? 0),
            'centre_search' => trim((string) ($validated['filter_centre_search'] ?? '')),
        ];

        return redirect()
            ->route('admin.statistics.index', array_filter(
                $returnFilters,
                fn ($value) => $value !== '' && $value !== 0 && $value !== 'ALL'
            ))
            ->withFragment('dispatching-centres')
            ->with('status', "{$updated} ligne(s) mise(s) à jour pour l’axe et le point de largage.");
    }

    public function updateBulk(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'centre_ecrit_id' => ['required', 'integer', 'exists:centre_ecrits,id'],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.effectif' => ['required', 'integer', 'min:0', 'max:1000'],
            'annee' => ['nullable', 'string', 'max:9'],
            'type_examen' => ['nullable', 'in:ALL,BEPC,CEPE'],
            'dren_id' => ['nullable', 'integer', 'min:0'],
            'cisco_id' => ['nullable', 'integer', 'min:0'],
            'centre_correction_id' => ['nullable', 'integer', 'min:0'],
            'centre_search' => ['nullable', 'string', 'max:255'],
        ]);

        $centreEcritId = (int) $validated['centre_ecrit_id'];
        $rowIds = collect($validated['rows'])->keys()->map(fn ($id) => (int) $id)->values();
        $returnFilters = [
            'annee' => (string) ($validated['annee'] ?? ''),
            'type_examen' => strtoupper((string) ($validated['type_examen'] ?? 'ALL')),
            'dren_id' => (int) ($validated['dren_id'] ?? 0),
            'cisco_id' => (int) ($validated['cisco_id'] ?? 0),
            'centre_correction_id' => (int) ($validated['centre_correction_id'] ?? 0),
            'centre_ecrit_id' => $centreEcritId,
            'centre_search' => trim((string) ($validated['centre_search'] ?? '')),
        ];

        $statsQuery = RepartitionSalle::query()
            ->where('centre_ecrit_id', $centreEcritId)
            ->whereIn('id', $rowIds);

        if ($returnFilters['annee'] !== '') {
            $statsQuery->where('annee', $returnFilters['annee']);
        }
        if ($returnFilters['type_examen'] === 'BEPC') {
            $statsQuery->where('langue', '!=', 'TOTAL');
        } elseif ($returnFilters['type_examen'] === 'CEPE') {
            $statsQuery->where('langue', 'TOTAL');
        }

        $stats = $statsQuery->get(['id', 'centre_ecrit_id']);

        if ($stats->count() !== $rowIds->count()) {
            return back()->withErrors(['stat' => 'Certaines lignes ne correspondent pas au centre sélectionné.']);
        }

        DB::transaction(function () use ($validated, $centreEcritId) {
            foreach ($validated['rows'] as $id => $row) {
                RepartitionSalle::query()
                    ->where('id', (int) $id)
                    ->where('centre_ecrit_id', $centreEcritId)
                    ->update(['effectif' => (int) $row['effectif']]);
            }
        });

        return redirect()
            ->route('admin.statistics.index', array_filter(
                $returnFilters,
                fn ($value) => $value !== '' && $value !== 0 && $value !== 'ALL'
            ))
            ->withFragment('modification-globale')
            ->with('status', 'Modification globale enregistrée pour le centre sélectionné.');
    }

    public function destroyCentre(Request $request, int $centreEcritId): RedirectResponse
    {
        $deleted = RepartitionSalle::query()
            ->where('centre_ecrit_id', $centreEcritId)
            ->delete();

        if ($deleted === 0) {
            return back()->withErrors(['stat' => 'Aucune statistique trouvée pour ce centre.']);
        }

        return back()->with('status', "Centre supprimé avec succès ({$deleted} lignes supprimées).");
    }

    private function buildBulkStatsForCentre(int $centreEcritId, array $filters): Collection
    {
        $query = RepartitionSalle::query()
            ->with('centreEcrit.centreCorrection.cisco.dren')
            ->where('centre_ecrit_id', $centreEcritId)
            ->orderBy('annee')
            ->orderBy('langue')
            ->orderBy('numero_salle');

        if ($filters['annee'] !== '') {
            $query->where('annee', $filters['annee']);
        }
        if ($filters['type_examen'] === 'BEPC') {
            $query->where('langue', '!=', 'TOTAL');
        } elseif ($filters['type_examen'] === 'CEPE') {
            $query->where('langue', 'TOTAL');
        }

        return $query->get();
    }

    private function buildDispatchingRows(array $filters): Collection
    {
        $query = RepartitionSalle::query()
            ->join('centre_ecrits as ce', 'ce.id', '=', 'repartition_salles.centre_ecrit_id')
            ->join('centre_corrections as cc', 'cc.id', '=', 'ce.centre_correction_id')
            ->join('ciscos as cs', 'cs.id', '=', 'cc.cisco_id')
            ->join('drens as d', 'd.id', '=', 'cs.dren_id')
            ->select([
                'repartition_salles.centre_ecrit_id',
                'repartition_salles.annee',
                'ce.type_examen',
                'd.nom as dren',
                'cs.nom as cisco',
                'cc.nom as centre_correction',
                'ce.nom as centre_ecrit',
                DB::raw("COALESCE(NULLIF(TRIM(repartition_salles.axe_dispatching), ''), '') as axe_dispatching"),
                DB::raw("COALESCE(NULLIF(TRIM(repartition_salles.point_largage), ''), '') as point_largage"),
                DB::raw('COUNT(*) as total_lignes'),
                DB::raw('COUNT(DISTINCT repartition_salles.numero_salle) as total_salles'),
                DB::raw('SUM(repartition_salles.effectif) as total_candidats'),
            ])
            ->when($filters['annee'] !== '', fn ($query) => $query->where('repartition_salles.annee', $filters['annee']))
            ->when($filters['type_examen'] === 'BEPC', fn ($query) => $query->where('repartition_salles.langue', '!=', 'TOTAL'))
            ->when($filters['type_examen'] === 'CEPE', fn ($query) => $query->where('repartition_salles.langue', 'TOTAL'))
            ->when($filters['dren_id'] > 0, fn ($query) => $query->where('cs.dren_id', $filters['dren_id']))
            ->when($filters['cisco_id'] > 0, fn ($query) => $query->where('cc.cisco_id', $filters['cisco_id']))
            ->when($filters['centre_correction_id'] > 0, fn ($query) => $query->where('ce.centre_correction_id', $filters['centre_correction_id']))
            ->when($filters['centre_ecrit_id'] > 0, fn ($query) => $query->where('repartition_salles.centre_ecrit_id', $filters['centre_ecrit_id']))
            ->when($filters['centre_search'] !== '', fn ($query) => $query->where('ce.nom', 'like', '%'.$filters['centre_search'].'%'))
            ->groupBy(
                'repartition_salles.centre_ecrit_id',
                'repartition_salles.annee',
                'ce.type_examen',
                'd.nom',
                'cs.nom',
                'cc.nom',
                'ce.nom',
            )
            ->groupByRaw("COALESCE(NULLIF(TRIM(repartition_salles.axe_dispatching), ''), '')")
            ->groupByRaw("COALESCE(NULLIF(TRIM(repartition_salles.point_largage), ''), '')")
            ->orderByDesc('repartition_salles.annee')
            ->orderBy('d.nom')
            ->orderBy('cs.nom')
            ->orderBy('ce.nom')
            ->limit(200);

        return $query->get();
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

    private function getExistingDispatchingValues(string $column): array
    {
        if (! in_array($column, ['axe_dispatching', 'point_largage'], true)) {
            return [];
        }

        return RepartitionSalle::query()
            ->select($column)
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->distinct()
            ->orderBy($column)
            ->pluck($column)
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn ($value) => $value !== '')
            ->unique()
            ->values()
            ->all();
    }
}
