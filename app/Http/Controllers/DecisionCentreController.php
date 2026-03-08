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

        // Fetch DRENs and CISCOs
        $drens = Dren::all();
        $ciscos = CISCO::all();

        // Query correction and written centers
        $centresCorrection = CentreCorrection::query();
        $centresEcrit = CentreEcrit::query();

        if ($typeExamen) {
            $centresCorrection->where('type_examen', $typeExamen);
            $centresEcrit->where('type_examen', $typeExamen);
        }

        if ($drenId) {
            $centresCorrection->where('cisco_id', $drenId);
            $centresEcrit->where('centre_correction_id', $drenId);
        }

        if ($ciscoId) {
            $centresCorrection->where('cisco_id', $ciscoId);
            $centresEcrit->where('centre_correction_id', $ciscoId);
        }

        $centresCorrection = $centresCorrection->get();
        $centresEcrit = $centresEcrit->get();

        // Build table data per DREN
        $tableData = [];
        foreach ($drens as $dren) {
            $ciscoCount = $ciscos->where('dren_id', $dren->id)->count();
            $correctionCount = $centresCorrection->where('cisco_id', $dren->id)->count();
            $ecritCount = $centresEcrit->where('centre_correction_id', $dren->id)->count();

            $tableData[] = [
                'dren' => $dren->nom,
                'cisco' => $ciscoCount,
                'correction' => $correctionCount,
                'ecrit' => $ecritCount,
            ];
        }

        // Totals
        $totalDren = $drens->count();
        $totalCisco = array_sum(array_column($tableData, 'cisco'));
        $totalCorrection = array_sum(array_column($tableData, 'correction'));
        $totalEcrit = array_sum(array_column($tableData, 'ecrit'));

        // Chart data for global chart
        $chartLabels = ['DREN', 'CISCO', 'Centre Correction', 'Centre Écrit'];
        $chartData = [$totalDren, $totalCisco, $totalCorrection, $totalEcrit];

        return view('decision.centre', compact(
            'drens', 'ciscos', 'tableData',
            'totalDren', 'totalCisco', 'totalCorrection', 'totalEcrit',
            'chartLabels', 'chartData', 'typeExamen', 'drenId', 'ciscoId'
        ));
    }
}