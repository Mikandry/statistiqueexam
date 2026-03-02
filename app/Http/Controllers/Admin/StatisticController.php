<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        ];

        if (! in_array($filters['type_examen'], ['ALL', 'BEPC', 'CEPE'], true)) {
            $filters['type_examen'] = 'ALL';
        }

        $statsQuery = RepartitionSalle::query()
            ->with('centreEcrit')
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

        $stats = $statsQuery->paginate(30)->withQueryString();
        $annees = RepartitionSalle::query()
            ->select('annee')
            ->distinct()
            ->orderByDesc('annee')
            ->pluck('annee');

        return view('admin.statistics.index', [
            'stats' => $stats,
            'filters' => $filters,
            'annees' => $annees,
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
