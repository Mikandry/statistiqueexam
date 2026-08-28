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
        $bulkStats = $filters['centre_ecrit_id'] > 0
            ? $this->buildBulkStatsForCentre($filters['centre_ecrit_id'], $filters)
            : collect();

        return view('admin.statistics.index', [
            'stats' => $stats,
            'filters' => $filters,
            'annees' => $annees,
            'drens' => $drens,
            'ciscos' => $ciscos,
            'centresCorrection' => $centresCorrection,
            'centresEcrit' => $centresEcrit,
            'bulkStats' => $bulkStats,
            'globalSetting' => GlobalSetting::query()->first(),
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

    public function updateBulk(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'centre_ecrit_id' => ['required', 'integer', 'exists:centre_ecrits,id'],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.effectif' => ['required', 'integer', 'min:0', 'max:1000'],
        ]);

        $centreEcritId = (int) $validated['centre_ecrit_id'];
        $rowIds = collect($validated['rows'])->keys()->map(fn ($id) => (int) $id)->values();

        $stats = RepartitionSalle::query()
            ->where('centre_ecrit_id', $centreEcritId)
            ->whereIn('id', $rowIds)
            ->get(['id', 'centre_ecrit_id']);

        if ($stats->count() !== $rowIds->count()) {
            return back()->withErrors(['stat' => 'Certaines lignes ne correspondent pas au centre sélectionné.']);
        }

        foreach ($validated['rows'] as $id => $row) {
            RepartitionSalle::query()
                ->where('id', (int) $id)
                ->where('centre_ecrit_id', $centreEcritId)
                ->update(['effectif' => (int) $row['effectif']]);
        }

        return back()->with('status', 'Modification globale enregistrée pour le centre sélectionné.');
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
}
