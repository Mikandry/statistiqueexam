<?php

namespace App\Http\Controllers;

use App\Models\HrAgent;
use App\Models\HrEvent;
use App\Services\HrActivityTotalsService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HrActivityTotalsController extends Controller
{
    public function __construct(private readonly HrActivityTotalsService $totals)
    {
    }

    public function index(Request $request)
    {
        [$from, $to] = $this->period($request);
        $search = trim((string) $request->query('q', ''));

        return view('hr.activity-totals', [
            'from' => $from, 'to' => $to, 'search' => $search,
            'rows' => $this->totals->totals($from, $to, $search ?: null),
            'overview' => $this->totals->overview($from, $to),
            'types' => HrEvent::TYPES,
            'priority' => HrActivityTotalsService::PRIORITY,
        ]);
    }

    public function show(Request $request, HrAgent $agent)
    {
        [$from, $to] = $this->period($request);
        $events = $agent->events()->whereIn('status', HrActivityTotalsService::COUNTED_STATUSES)
            ->whereDate('date_debut', '<=', $to)
            ->where(fn ($w) => $w->whereNull('date_fin')->orWhereDate('date_fin', '>=', $from))
            ->orderBy('date_debut')->get();

        return view('hr.activity-agent', [
            'from' => $from, 'to' => $to, 'agent' => $agent, 'events' => $events,
            'row' => $this->totals->forAgent($agent, $events, $from, $to),
            'types' => HrEvent::TYPES,
        ]);
    }

    /** @return array{0:Carbon,1:Carbon} */
    private function period(Request $request): array
    {
        $request->validate(['from' => ['nullable', 'date'], 'to' => ['nullable', 'date']]);
        $from = $request->query('from') ? Carbon::parse($request->query('from'))->startOfDay() : Carbon::now()->startOfYear();
        $to = $request->query('to') ? Carbon::parse($request->query('to'))->startOfDay() : Carbon::now()->startOfDay();
        if ($to->lt($from)) {
            [$from, $to] = [$to, $from];
        }

        return [$from, $to];
    }
}
