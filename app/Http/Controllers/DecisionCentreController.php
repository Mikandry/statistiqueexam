<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dren;
use App\Models\CISCO;
use App\Models\CentreCorrection;
use App\Models\CentreEcrit;

class DecisionCentreController extends Controller
{
    public function index(Request $request)
    {
        $typeExamen = $request->get('type_examen');
        $drenId = $request->get('dren');
        $ciscoId = $request->get('cisco');

        // Fetch DRENs and CISCOs (filter CISCOs by DREN when selected)
        $drens = Dren::all();
        $ciscosQuery = CISCO::query();
        if ($drenId) {
            $ciscosQuery->where('dren_id', $drenId);
        }
        $ciscos = $ciscosQuery->get();
        if ($drenId && $ciscoId && ! $ciscos->pluck('id')->contains((int) $ciscoId)) {
            $ciscoId = null;
        }

        // Query correction and written centers
        $centresCorrection = CentreCorrection::query()->with('cisco.dren');
        $centresEcrit = CentreEcrit::query()->with('centreCorrection.cisco.dren');

        if ($typeExamen) {
            $centresCorrection->where('type_examen', $typeExamen);
            $centresEcrit->where('type_examen', $typeExamen);
        }

        if ($drenId) {
            $centresCorrection->whereHas('cisco', function ($query) use ($drenId) {
                $query->where('dren_id', $drenId);
            });
            $centresEcrit->whereHas('centreCorrection.cisco', function ($query) use ($drenId) {
                $query->where('dren_id', $drenId);
            });
        }

        if ($ciscoId) {
            $centresCorrection->where('cisco_id', $ciscoId);
            $centresEcrit->whereHas('centreCorrection', function ($query) use ($ciscoId) {
                $query->where('cisco_id', $ciscoId);
            });
        }

        $centresCorrection = $centresCorrection->get();
        $centresEcrit = $centresEcrit->get()->unique('id')->values();

        // Build table data with imported centres
        $tableData = [];
        $ecritsByCorrection = $centresEcrit->groupBy('centre_correction_id');
        foreach ($centresCorrection as $centreCorrection) {
            $drenNom = $centreCorrection->cisco->dren->nom ?? '-';
            $ciscoNom = $centreCorrection->cisco->nom ?? '-';
            $ecrits = $ecritsByCorrection->get($centreCorrection->id, collect())->unique('id');

            if ($ecrits->isEmpty()) {
                $tableData[] = [
                    'correction_id' => $centreCorrection->id,
                    'ecrit_id' => null,
                    'dren' => $drenNom,
                    'cisco' => $ciscoNom,
                    'correction' => $centreCorrection->nom,
                    'ecrit' => '-',
                ];
                continue;
            }

            foreach ($ecrits as $centreEcrit) {
                $tableData[] = [
                    'correction_id' => $centreCorrection->id,
                    'ecrit_id' => $centreEcrit->id,
                    'dren' => $drenNom,
                    'cisco' => $ciscoNom,
                    'correction' => $centreCorrection->nom,
                    'ecrit' => $centreEcrit->nom,
                ];
            }
        }

        $tableData = collect($tableData)
            ->unique(fn ($row) => implode('|', [$row['correction_id'], $row['ecrit_id'] ?? 'none']))
            ->values()
            ->all();

        // Totals based on filtered data
        $totalDren = $centresCorrection
            ->map(fn ($cc) => $cc->cisco->dren->id ?? null)
            ->filter()
            ->unique()
            ->count();
        $totalCisco = $centresCorrection->pluck('cisco_id')->unique()->count();
        $totalCorrection = $centresCorrection->count();
        $totalEcrit = $centresEcrit->count();

        return view('decision.centre', compact(
            'drens', 'ciscos', 'tableData',
            'totalDren', 'totalCisco', 'totalCorrection', 'totalEcrit',
            'typeExamen', 'drenId', 'ciscoId'
        ));
    }
}
