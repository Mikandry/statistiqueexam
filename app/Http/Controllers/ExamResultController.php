<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Cisco;
use App\Models\Dren;
use App\Models\ExamResult;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExamResultController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', ExamResult::class);

        $filters = $request->only(['year', 'exam_name', 'dren_id', 'cisco_id', 'status', 'published_at', 'search']);
        $query = ExamResult::query()->with(['dren', 'cisco'])->filtered($filters);
        $allResults = (clone $query)->get();
        $results = $query->orderBy('year', 'desc')->orderBy('exam_name')->paginate(20)->withQueryString();

        $ciscoTotal = Cisco::query()->count();
        $stats = $this->buildStats($allResults, $ciscoTotal);
        $rankings = $this->buildRankings($allResults);
        $charts = $this->buildCharts($allResults);

        return view('exam-results.index', [
            'results' => $results,
            'stats' => $stats,
            'rankings' => $rankings,
            'charts' => $charts,
            'drens' => Dren::query()->orderBy('nom')->get(),
            'ciscos' => Cisco::query()->with('dren')->orderBy('nom')->get(),
            'filters' => $filters,
            'statuses' => [
                ExamResult::STATUS_PENDING => 'En attente',
                ExamResult::STATUS_IN_PROGRESS => 'En cours',
                ExamResult::STATUS_PUBLISHED => 'Publié',
            ],
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', ExamResult::class);

        $data = $this->validatedData($request);
        $cisco = Cisco::query()->with('dren')->findOrFail($data['cisco_id']);
        $data['dren_id'] = $cisco->dren_id;
        $data['created_by'] = $request->user()->id;

        $result = ExamResult::query()->create($data);
        AuditLog::record($request, 'exam_result_created', ['result_id' => $result->id]);

        return back()->with('success', 'Résultat enregistré avec succès.');
    }

    public function update(Request $request, ExamResult $examResult)
    {
        $this->authorize('update', $examResult);

        $data = $this->validatedData($request);
        $cisco = Cisco::query()->findOrFail($data['cisco_id']);
        $data['dren_id'] = $cisco->dren_id;

        $examResult->update($data);
        AuditLog::record($request, 'exam_result_updated', ['result_id' => $examResult->id]);

        return back()->with('success', 'Résultat mis à jour.');
    }

    public function destroy(Request $request, ExamResult $examResult)
    {
        $this->authorize('delete', $examResult);

        $examResult->delete();
        AuditLog::record($request, 'exam_result_deleted', ['result_id' => $examResult->id]);

        return back()->with('success', 'Résultat supprimé.');
    }

    public function publish(Request $request, ExamResult $examResult)
    {
        $this->authorize('publish', $examResult);

        $examResult->update(['published_at' => now()]);
        AuditLog::record($request, 'exam_result_published', ['result_id' => $examResult->id]);

        return back()->with('success', 'Résultat publié.');
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', ExamResult::class);

        $results = ExamResult::query()
            ->with(['dren', 'cisco'])
            ->filtered($request->only(['year', 'exam_name', 'dren_id', 'cisco_id', 'status', 'published_at', 'search']))
            ->orderBy('year', 'desc')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            'Année', 'Examen', 'Région', 'CISCO', 'Candidats', 'Absents', 'Présents',
            'Admis', 'Seuil', 'Taux réussite', 'Taux abandon', 'Publication', 'Statut',
        ], null, 'A1');

        foreach ($results as $index => $result) {
            $sheet->fromArray([
                $result->year,
                $result->exam_name,
                $result->dren?->nom,
                $result->cisco?->nom,
                $result->total_candidates,
                $result->absent_candidates,
                $result->present_candidates,
                $result->admitted_candidates,
                $result->admission_threshold,
                $result->success_rate,
                $result->abandonment_rate,
                $result->published_at?->format('d/m/Y H:i'),
                $this->statusLabel($result->status),
            ], null, 'A'.($index + 2));
        }

        return response()->streamDownload(function () use ($spreadsheet): void {
            (new Xlsx($spreadsheet))->save('php://output');
        }, 'resultats-examens.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $this->authorize('viewAny', ExamResult::class);

        $results = ExamResult::query()
            ->with(['dren', 'cisco'])
            ->filtered($request->only(['year', 'exam_name', 'dren_id', 'cisco_id', 'status', 'published_at', 'search']))
            ->orderBy('year', 'desc')
            ->get();

        return Pdf::loadView('exam-results.pdf', [
            'results' => $results,
            'stats' => $this->buildStats($results, Cisco::query()->count()),
        ])->setPaper('a4', 'landscape')->download('resultats-examens.pdf');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'year' => ['required', 'integer', 'between:2000,2100'],
            'exam_name' => ['required', 'string', 'max:120'],
            'cisco_id' => ['required', 'exists:ciscos,id'],
            'total_candidates' => ['required', 'integer', 'min:0'],
            'absent_candidates' => ['nullable', 'integer', 'min:0', 'lte:total_candidates'],
            'admitted_candidates' => ['nullable', 'integer', 'min:0'],
            'admission_threshold' => ['nullable', 'numeric', 'min:0', 'max:20'],
            'published_at' => ['nullable', 'date'],
            'observations' => ['nullable', 'string', 'max:5000'],
        ]);
    }

    private function buildStats($results, int $ciscoTotal): array
    {
        $published = $results->where('status', ExamResult::STATUS_PUBLISHED)->count();
        $pending = max(0, $ciscoTotal - $results->where('status', '!=', ExamResult::STATUS_PUBLISHED)->count() - $published)
            + $results->where('status', ExamResult::STATUS_PENDING)->count();
        $inProgress = $results->where('status', ExamResult::STATUS_IN_PROGRESS)->count();
        $totalCandidates = $results->sum('total_candidates');
        $presents = $results->sum('present_candidates');
        $admitted = $results->sum('admitted_candidates');
        $absents = $results->sum('absent_candidates');

        return [
            'cisco_total' => $ciscoTotal,
            'published' => $published,
            'pending' => $pending,
            'in_progress' => $inProgress,
            'published_percent' => $ciscoTotal > 0 ? round(($published / $ciscoTotal) * 100, 2) : 0,
            'total_candidates' => $totalCandidates,
            'present_candidates' => $presents,
            'admitted_candidates' => $admitted,
            'absent_candidates' => $absents,
            'national_success_rate' => $presents > 0 ? round(($admitted / $presents) * 100, 2) : 0,
            'national_abandonment_rate' => $totalCandidates > 0 ? round(($absents / $totalCandidates) * 100, 2) : 0,
        ];
    }

    private function buildRankings($results): array
    {
        $published = $results->where('status', ExamResult::STATUS_PUBLISHED)->filter(fn ($item) => $item->present_candidates > 0);
        $regions = $published->groupBy('dren_id')->map(function ($items) {
            $presents = $items->sum('present_candidates');
            $admitted = $items->sum('admitted_candidates');

            return [
                'name' => $items->first()->dren?->nom,
                'presents' => $presents,
                'admitted' => $admitted,
                'rate' => $presents > 0 ? round(($admitted / $presents) * 100, 2) : 0,
            ];
        })->sortByDesc('rate')->values();

        return [
            'best_cisco' => $published->sortByDesc('success_rate')->first(),
            'worst_cisco' => $published->sortBy('success_rate')->first(),
            'best_region' => $regions->first(),
            'worst_region' => $regions->last(),
            'top_ciscos' => $published->sortByDesc('success_rate')->take(10)->values(),
            'low_ciscos' => $published->sortBy('success_rate')->take(10)->values(),
            'top_regions' => $regions->take(10),
            'low_regions' => $regions->sortBy('rate')->take(10)->values(),
        ];
    }

    private function buildCharts($results): array
    {
        $published = $results->where('status', ExamResult::STATUS_PUBLISHED);

        return [
            'publications' => $published->groupBy(fn ($item) => $item->published_at?->format('Y-m-d') ?? 'Non daté')
                ->map->count(),
            'regions' => $published->groupBy('dren.nom')->map(fn ($items) => $items->sum('admitted_candidates')),
            'success_by_region' => $published->groupBy('dren.nom')->map(function ($items) {
                $presents = $items->sum('present_candidates');
                return $presents > 0 ? round(($items->sum('admitted_candidates') / $presents) * 100, 2) : 0;
            }),
            'best' => $published->sortByDesc('success_rate')->take(8)->mapWithKeys(fn ($item) => [$item->cisco?->nom => (float) $item->success_rate]),
        ];
    }

    private function statusLabel(string $status): string
    {
        return [
            ExamResult::STATUS_PENDING => 'En attente',
            ExamResult::STATUS_IN_PROGRESS => 'En cours',
            ExamResult::STATUS_PUBLISHED => 'Publié',
        ][$status] ?? $status;
    }
}
