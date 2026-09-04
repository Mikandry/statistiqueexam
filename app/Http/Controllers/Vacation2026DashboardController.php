<?php

namespace App\Http\Controllers;

use App\Models\Dren;
use App\Models\Cisco;
use App\Services\Vacation2026DashboardService;
use App\Services\VacationDecreeService;
use Illuminate\Http\Request;

/**
 * Vacation2026DashboardController
 *
 * Handles all dashboard views for the Vacation 2026 module:
 * - MEN Central Dashboard
 * - DREN Dashboard
 * - CISCO Dashboard
 * - Centre Dashboard
 * - EPS/GYM Dashboard
 * - Global Vacation 2026 Dashboard
 */
class Vacation2026DashboardController extends Controller
{
    public function __construct(
        private readonly Vacation2026DashboardService $dashboardService,
        private readonly VacationDecreeService $decree
    ) {
    }

    /**
     * MEN Central Dashboard
     */
    public function menCentral(Request $request)
    {
        $examFilter = $request->query('exam', '');
        $phaseFilter = $request->query('phase', '');

        $activityId = $request->query('activity_id') ? (int) $request->query('activity_id') : null;
        $data = $this->dashboardService->menCentralDashboard($examFilter, $phaseFilter, $activityId);

        return view('vacation-2026.dashboards.men-central', array_merge($data, [
            'examFilter' => $examFilter,
            'phaseFilter' => $phaseFilter,
            'activityFilter' => $activityId,
            'filterActivities' => \App\Models\Vacation2026Activity::where('level', 'CENTRAL')->where('year', '2026')->orderBy('examen')->orderBy('ordre')->get(),
            'exams' => ['CEPE', 'BEPC'],
            'phases' => ['AVANT_SESSION', 'PENDANT_SESSION', 'APRES_SESSION'],
        ]));
    }

    /**
     * DREN Dashboard
     */
    public function dren(Request $request)
    {
        $drenId = $request->query('dren_id');
        $ciscoId = $request->query('cisco_id');
        $filters = $this->dashboardFilters($request);

        $dashboards = $this->dashboardService->drenDashboard($drenId ? (int)$drenId : null, $ciscoId ? (int)$ciscoId : null, $filters[0], $filters[1], $filters[2]);

        // If specific DREN requested, show single dashboard
        if ($drenId && ! empty($dashboards)) {
            $data = $dashboards[0];
            return view('vacation-2026.dashboards.dren-single', array_merge($data, [
                'allDrens' => Dren::all(['id', 'nom']),
                'allCiscos' => Cisco::where('dren_id', $data['dren_id'])->get(['id', 'nom']),
                'selectedDrenId' => $drenId,
                'selectedCiscoId' => $ciscoId,
                ...$this->filterViewData($filters, 'DREN'),
            ]));
        }

        // Otherwise show list of all DRENs
        return view('vacation-2026.dashboards.dren-list', [
            'dashboards' => $dashboards,
            'selectedDrenId' => $drenId,
            'allDrens' => Dren::all(['id', 'nom']),
            'allCiscos' => Cisco::with('dren')->get(['id', 'nom', 'dren_id']),
            ...$this->filterViewData($filters, 'DREN'),
        ]);
    }

    /**
     * CISCO Dashboard
     */
    public function cisco(Request $request)
    {
        $ciscoId = $request->query('cisco_id');
        $centreId = $request->query('centre_id');
        $filters = $this->dashboardFilters($request);

        $dashboards = $this->dashboardService->ciscoDashboard($ciscoId ? (int)$ciscoId : null, $filters[0], $filters[1], $filters[2], $centreId ? (int)$centreId : null);

        // If specific CISCO requested, show single dashboard
        if ($ciscoId && ! empty($dashboards)) {
            $data = $dashboards[0];
            return view('vacation-2026.dashboards.cisco-single', array_merge($data, [
                'allCiscos' => Cisco::with('dren')->get(['id', 'nom', 'dren_id']),
                'allCentres' => \App\Models\CentreCorrection::where('cisco_id', $data['cisco_id'])->get(['id', 'nom']),
                'selectedCiscoId' => $ciscoId,
                'selectedCentreId' => $centreId,
                ...$this->filterViewData($filters, 'CISCO'),
            ]));
        }

        // Otherwise show list of all CISCOs
        return view('vacation-2026.dashboards.cisco-list', [
            'dashboards' => $dashboards,
            'selectedCiscoId' => $ciscoId,
            'allCiscos' => Cisco::with('dren')->get(['id', 'nom', 'dren_id']),
            'allCentres' => \App\Models\CentreCorrection::all(['id', 'nom']),
            ...$this->filterViewData($filters, 'CISCO'),
        ]);
    }

    /**
     * Centre Dashboard
     */
    // public function centre(Request $request)
    // {
    //     $centreId = $request->query('centre_id');

    //     $dashboards = $this->dashboardService->centreDashboard($centreId ? (int)$centreId : null);

    //     // If specific centre requested, show single dashboard
    //     if ($centreId && ! empty($dashboards)) {
    //         $data = $dashboards[0];
    //         return view('vacation-2026.dashboards.centre-single', array_merge($data, [
    //             'allCentres' => CentreCorrection::all(['id', 'nom']),
    //             'selectedCentreId' => $centreId,
    //         ]));
    //     }

    //     // Otherwise show list of all centres
    //     return view('vacation-2026.dashboards.centre-list', [
    //         'dashboards' => $dashboards,
    //         'selectedCentreId' => $centreId,
    //     ]);
    // }
    public function centre(Request $request)
{
    $centreId = $request->query('centre_id');
    $filters = $this->dashboardFilters($request);

    $dashboards = $this->dashboardService->centreDashboard(
        $centreId ? (int) $centreId : null,
        null,
        $filters[0],
        $filters[1],
        $filters[2]
    );

    // If a specific centre is requested
    if ($centreId && !empty($dashboards)) {
        $data = $dashboards[0];

        return view('vacation-2026.dashboards.centre-single', array_merge($data, [
                'allCentres' => $this->dashboardService->centreDashboard(),
            'selectedCentreId' => $centreId,
            ...$this->filterViewData($filters, 'CENTRE'),
        ]));
    }

    // Divide centres into the 3 categories
    $ecritOnly = collect($dashboards)->filter(function ($dashboard) {
        return ($dashboard['centre_type'] ?? null) === VacationDecreeService::CENTRE_TYPE_ECRIT;
    });

    $correctionOnly = collect($dashboards)->filter(function ($dashboard) {
        return ($dashboard['centre_type'] ?? null) === VacationDecreeService::CENTRE_TYPE_CORRECTION;
    });

    $jumeles = collect($dashboards)->filter(function ($dashboard) {
        return ($dashboard['centre_type'] ?? null) === VacationDecreeService::CENTRE_TYPE_JUMELES;
    });

    return view('vacation-2026.dashboards.centre-list', [
        'dashboards' => $dashboards,
        'ecritOnly' => $ecritOnly,
        'correctionOnly' => $correctionOnly,
        'jumeles' => $jumeles,
        'selectedCentreId' => $centreId,
        ...$this->filterViewData($filters, 'CENTRE'),
    ]);
}

    /**
     * EPS/GYM Dashboard
     */
    public function eps(Request $request)
    {
        $filters = $this->dashboardFilters($request);
        $data = $this->dashboardService->epsDashboard($filters[0], $filters[1], $filters[2], $request->query('cisco_id') ? (int) $request->query('cisco_id') : null);

        return view('vacation-2026.dashboards.eps', array_merge($data, $this->filterViewData($filters, 'EPS'), [
            'allCiscos' => Cisco::with('dren')->get(['id', 'nom', 'dren_id']),
            'selectedCiscoId' => $request->query('cisco_id'),
        ]));
    }

    /**
     * Global Vacation 2026 Dashboard
     */
    public function global(Request $request)
    {
        $filters = $this->dashboardFilters($request);
        $data = $this->dashboardService->globalDashboard(
            $filters[0],
            $filters[1],
            $filters[2],
            $request->query('dren_id') ? (int) $request->query('dren_id') : null,
            $request->query('cisco_id') ? (int) $request->query('cisco_id') : null,
            $request->query('centre_id') ? (int) $request->query('centre_id') : null
        );

        return view('vacation-2026.dashboards.global', array_merge($data, $this->filterViewData($filters, 'GLOBAL'), [
            'allDrens' => Dren::all(['id', 'nom']),
            'allCiscos' => Cisco::with('dren')->get(['id', 'nom', 'dren_id']),
            'allCentres' => \App\Models\CentreCorrection::all(['id', 'nom']),
            'selectedDrenId' => $request->query('dren_id'),
            'selectedCiscoId' => $request->query('cisco_id'),
            'selectedCentreId' => $request->query('centre_id'),
        ]));
    }

    private function dashboardFilters(Request $request): array
    {
        return [
            (string) $request->query('exam', ''),
            (string) $request->query('phase', ''),
            $request->query('activity_id') ? (int) $request->query('activity_id') : null,
        ];
    }

    private function filterViewData(array $filters, string $level): array
    {
        [$exam, $phase, $activityId] = $filters;
        return [
            'examFilter' => $exam,
            'phaseFilter' => $phase,
            'activityFilter' => $activityId,
            'filterActivities' => \App\Models\Vacation2026Activity::when($level !== 'GLOBAL', fn ($query) => $query->where('level', $level))->where('year', '2026')->orderBy('examen')->orderBy('ordre')->get(),
            'exams' => ['CEPE', 'BEPC', 'EPS'],
            'phases' => ['AVANT_SESSION', 'PENDANT_SESSION', 'APRES_SESSION', 'AVANT_EPREUVES_EPS', 'PENDANT_EPREUVES_EPS', 'APRES_EPREUVES_EPS'],
        ];
    }
}