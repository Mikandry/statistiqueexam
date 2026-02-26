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
        $stats = RepartitionSalle::query()
            ->with('centreEcrit')
            ->orderByDesc('annee')
            ->orderBy('centre_ecrit_id')
            ->orderBy('langue')
            ->orderBy('numero_salle')
            ->paginate(30)
            ->withQueryString();

        return view('admin.statistics.index', [
            'stats' => $stats,
        ]);
    }

    public function update(Request $request, RepartitionSalle $stat): RedirectResponse
    {
        $validated = $request->validate([
            'annee' => ['required', 'regex:/^\d{4}-\d{4}$/'],
            'langue' => ['required', 'string', 'max:255'],
            'numero_salle' => ['required', 'integer', 'min:1', 'max:200'],
            'effectif' => ['required', 'integer', 'min:0', 'max:1000'],
        ]);

        $stat->update($validated);

        return back()->with('status', 'Statistique modifiée.');
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
