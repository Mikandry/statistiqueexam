<?php

namespace App\Http\Controllers;

use App\Services\HrAvailabilityService;
use Illuminate\Http\Request;

class HrDashboardController extends Controller
{
    public function __construct(private readonly HrAvailabilityService $availability) {}

    public function index(Request $request)
    {
        $data = $this->availability->dashboard($request->date('date') ?: today());
        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $data['situations'] = $data['situations']->filter(fn ($s) => str_contains(mb_strtolower($s['agent']->full_name), mb_strtolower($search)) || str_contains(mb_strtolower((string) $s['agent']->matricule), mb_strtolower($search)))->values();
        }
        return view('hr.dashboard', $data + ['selectedDate' => $request->query('date', today()->toDateString()), 'search' => $search]);
    }
}
