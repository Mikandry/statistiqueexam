<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CentreCorrection;
use App\Models\Cisco;
use App\Models\Dren;
use App\Models\RepartitionSalle;
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

        $stats = $statsQuery->paginate(30)->withQueryString();
        $annees = RepartitionSalle::query()
            ->select('annee')
            ->distinct()
            ->orderByDesc('annee')
            ->pluck('annee');
        $drens = Dren::query()->orderBy('nom')->get(['id', 'nom']);
        $ciscos = Cisco::query()
            ->with('dren')
            ->when($filters['dren_id'] > 0, fn ($query) => $query->where('dren_id', $filters['dren_id']))
            ->orderBy('nom')
            ->get(['id', 'dren_id', 'nom']);
        $centresCorrection = CentreCorrection::query()
            ->with('cisco.dren')
            ->when($filters['dren_id'] > 0, fn ($query) => $query->whereHas('cisco', fn ($q) => $q->where('dren_id', $filters['dren_id'])))
            ->when($filters['cisco_id'] > 0, fn ($query) => $query->where('cisco_id', $filters['cisco_id']))
            ->orderBy('nom')
            ->get(['id', 'cisco_id', 'nom', 'type_examen']);

        return view('admin.statistics.index', [
            'stats' => $stats,
            'filters' => $filters,
            'annees' => $annees,
            'drens' => $drens,
            'ciscos' => $ciscos,
            'centresCorrection' => $centresCorrection,
        ]);
    }

    public function update(Request $request, RepartitionSalle $stat): RedirectResponse
    {
        $validated = $request->validate([
            'effectif' => ['required', 'integer', 'min:0', 'max:1000'],
        ]);

        $stat->update($validated);

        return back()->with('status', 'Effectif modifié. Salle/année/langue verrouillées.');
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
}
