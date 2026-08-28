<?php

namespace App\Http\Controllers;

use App\Models\CentreDecision;
use App\Models\CentreEcrit;
use App\Models\Cisco;
use App\Models\Dren;
use App\Models\RepartitionSalle;
use Illuminate\Http\Request;

class DecisionCentreController extends Controller
{
    public function index(Request $request)
    {
        $typeExamen = $request->get('type_examen');
        $drenId = $request->get('dren');
        $ciscoId = $request->get('cisco');
        $selectedAnnee = trim((string) $request->get('annee', ''));

        $annees = RepartitionSalle::query()
            ->select('annee')
            ->whereNotNull('annee')
            ->where('annee', '<>', '')
            ->distinct()
            ->orderByDesc('annee')
            ->pluck('annee')
            ->filter(fn ($annee) => filled($annee))
            ->values();

        $suggestedNextYear = (string) ((int) date('Y') + 1);

        if ($selectedAnnee === '' && $annees->isNotEmpty()) {
            $selectedAnnee = (string) $annees->first();
        }

        $isNewSession = false;
        if (filled($selectedAnnee) && ! $annees->contains($selectedAnnee)) {
            $isNewSession = true;
            $annees->prepend($selectedAnnee);
        }

        $drens = Dren::query()->orderBy('nom')->get();
        $ciscosQuery = Cisco::query();
        if ($drenId) {
            $ciscosQuery->where('dren_id', $drenId);
        }
        $ciscos = $ciscosQuery->orderBy('nom')->get();
        if ($drenId && $ciscoId && ! $ciscos->pluck('id')->contains((int) $ciscoId)) {
            $ciscoId = null;
        }

        $activeDecisions = CentreDecision::getActiveForSession($selectedAnnee, $typeExamen)
            ->load(['centreCorrection.cisco.dren', 'centreEcrit']);

        $tableData = $activeDecisions
            ->map(function (CentreDecision $decision) {
                return [
                    'correction_id' => $decision->centre_correction_id,
                    'ecrit_id' => $decision->centre_ecrit_id,
                    'dren' => $decision->centreCorrection?->cisco?->dren?->nom ?? '-',
                    'cisco' => $decision->centreCorrection?->cisco?->nom ?? '-',
                    'correction' => $decision->centreCorrection?->nom ?? '-',
                    'ecrit' => $decision->centreEcrit?->nom ?? '-',
                ];
            })
            ->values()
            ->all();

        $previousSessionHasDecisions = false;
        $hasImportedPreviousSession = false;
        $previousSessionYear = CentreDecision::resolvePreviousYear($selectedAnnee);

        if ($previousSessionYear !== null) {
            $previousDecisions = CentreDecision::query()
                ->where('annee', $previousSessionYear)
                ->where('actif', true)
                ->when(filled($typeExamen), fn ($query) => $query->where('type_examen', $typeExamen))
                ->pluck('centre_ecrit_id')
                ->unique();

            $currentDecisionIds = $activeDecisions
                ->pluck('centre_ecrit_id')
                ->filter()
                ->unique();

            $hasImportedPreviousSession = $previousDecisions->intersect($currentDecisionIds)->isNotEmpty();
        }

        $activeCentreEcritIds = $activeDecisions->pluck('centre_ecrit_id')->filter()->unique()->values();

        $availableCentres = CentreEcrit::query()
            ->with('centreCorrection.cisco.dren')
            ->when($typeExamen, fn ($query) => $query->where('type_examen', $typeExamen))
            ->when($drenId, fn ($query) => $query->whereHas('centreCorrection.cisco', fn ($subQuery) => $subQuery->where('dren_id', $drenId)))
            ->when($ciscoId, fn ($query) => $query->whereHas('centreCorrection', fn ($subQuery) => $subQuery->where('cisco_id', $ciscoId)))
            ->orderBy('nom')
            ->get();

        $centresSaisis = $activeDecisions
            ->map(function (CentreDecision $decision) {
                return [
                    'id' => $decision->id,
                    'nom' => $decision->centreEcrit?->nom ?? '-',
                    'region' => $decision->centreCorrection?->cisco?->dren?->nom ?? '-',
                ];
            })
            ->values();

        $centresNonSaisis = $availableCentres
            ->filter(fn (CentreEcrit $centre) => ! $activeCentreEcritIds->contains($centre->id))
            ->map(function (CentreEcrit $centre) {
                return [
                    'id' => $centre->id,
                    'nom' => $centre->nom,
                    'region' => $centre->centreCorrection?->cisco?->dren?->nom ?? '-',
                ];
            })
            ->values();

        $totalDren = collect($tableData)
            ->pluck('dren')
            ->filter(fn ($value) => filled($value) && $value !== '-')
            ->unique()
            ->count();
        $totalCisco = collect($tableData)
            ->pluck('cisco')
            ->filter(fn ($value) => filled($value) && $value !== '-')
            ->unique()
            ->count();
        $totalCorrection = collect($tableData)
            ->pluck('correction')
            ->filter(fn ($value) => filled($value) && $value !== '-')
            ->unique()
            ->count();
        $totalEcrit = collect($tableData)->count();

        return view('decision.centre', compact(
            'drens', 'ciscos', 'tableData',
            'totalDren', 'totalCisco', 'totalCorrection', 'totalEcrit',
            'typeExamen', 'drenId', 'ciscoId', 'annees', 'selectedAnnee',
            'centresSaisis', 'centresNonSaisis', 'isNewSession', 'suggestedNextYear',
            'hasImportedPreviousSession'
        ));
    }

    public function import(Request $request)
    {
        $annee = trim((string) $request->input('annee', ''));
        $sourceAnnee = trim((string) $request->input('source_annee', ''));
        $typeExamen = trim((string) $request->input('type_examen', ''));

        if ($annee === '') {
            return back()->withErrors(['annee' => 'Veuillez sélectionner une année de session.']);
        }

        $importedCount = CentreDecision::importFromPreviousSession($annee, $sourceAnnee !== '' ? $sourceAnnee : null, $typeExamen !== '' ? $typeExamen : null);

        if ($importedCount === 0) {
            return back()->with('status', 'Aucune décision de centre n’a été trouvée à importer pour cette session.');
        }

        return back()->with('status', "{$importedCount} centre(s) importé(s) vers la session {$annee}.");
    }

    public function storeCentre(Request $request)
    {
        $annee = trim((string) $request->input('annee', ''));
        $centreEcritId = (int) $request->input('centre_ecrit_id', 0);
        $typeExamen = trim((string) $request->input('type_examen', ''));

        if ($annee === '' || $centreEcritId <= 0) {
            return back()->withErrors(['centre_ecrit_id' => 'Veuillez sélectionner un centre à ajouter.']);
        }

        $centreEcrit = CentreEcrit::query()->findOrFail($centreEcritId);

        CentreDecision::updateOrCreate(
            [
                'annee' => $annee,
                'centre_ecrit_id' => $centreEcrit->id,
            ],
            [
                'centre_correction_id' => $centreEcrit->centre_correction_id,
                'type_examen' => $typeExamen !== '' ? $typeExamen : $centreEcrit->type_examen,
                'actif' => true,
            ]
        );

        return back()->with('status', 'Centre ajouté pour cette session.');
    }

    public function destroyCentre(CentreDecision $centreDecision, Request $request)
    {
        $annee = trim((string) $request->input('annee', ''));
        if ($annee !== '' && $centreDecision->annee !== $annee) {
            $centreDecision->fill(['annee' => $annee]);
        }

        $centreDecision->update(['actif' => false]);

        return back()->with('status', 'Centre désactivé pour cette session.');
    }
}

