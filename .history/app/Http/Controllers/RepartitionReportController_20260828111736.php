<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\BepcCopyDistribution;
use App\Models\GlobalSetting;
use App\Models\RepartitionSalle;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RepartitionReportController extends Controller
{
    private const TYPE_ALL = 'ALL';

    private const TYPE_BEPC = 'BEPC';

    private const TYPE_CEPE = 'CEPE';

    private const CEPE_KEY = 'TOTAL';

    private const MAX_SALLES_PER_TABLE = 20;

    private const CENTRES_PER_PAGE = 2;

    private const GE_PE_BEPC = 3;

    private const GE_PE_CEPE = 6;

    private const SUBJECT_PE_MARGIN = 5;

    public function dashboard(Request $request)
    {
        [$rows, $filters, $annees, $drens, $ciscos, $ciscosByDren] = $this->getFilteredRows($request);
        $centresSaisieStats = $this->getCentresSaisieStats($filters);

        $specialCandidates = DB::table('repartition_salles_specifiques as rss')
            ->join('centre_ecrits as ce', 'ce.id', '=', 'rss.centre_ecrit_id')
            ->join('centre_corrections as cc', 'cc.id', '=', 'ce.centre_correction_id')
            ->join('ciscos as cs', 'cs.id', '=', 'cc.cisco_id')
            ->join('drens as d', 'd.id', '=', 'cs.dren_id')
            ->select([
                'rss.id',
                'rss.centre_ecrit_id',
                'rss.type_examen',
                'rss.annee',
                'd.nom as dren',
                'cs.nom as cisco',
                'cc.nom as centre_correction',
                'ce.nom as centre_ecrit',
                'rss.numero_salle',
                'rss.type_handicap',
                'rss.saisi_par',
            ])
            ->when($filters['annee'] !== '', fn ($query) => $query->where('rss.annee', $filters['annee']))
            ->when($filters['dren'] !== '', fn ($query) => $query->where('d.nom', $filters['dren']))
            ->when($filters['cisco'] !== '', fn ($query) => $query->where('cs.nom', $filters['cisco']))
            ->when($filters['centre_search'] !== '', fn ($query) => $query->where('ce.nom', 'like', '%'.$filters['centre_search'].'%'))
            ->when($filters['type_examen'] === self::TYPE_BEPC, fn ($query) => $query->where('rss.type_examen', self::TYPE_BEPC))
            ->when($filters['type_examen'] === self::TYPE_CEPE, fn ($query) => $query->where('rss.type_examen', self::TYPE_CEPE))
            ->orderBy('rss.annee')
            ->orderBy('d.nom')
            ->orderBy('cs.nom')
            ->orderBy('cc.nom')
            ->orderBy('ce.nom')
            ->orderBy('rss.numero_salle')
            ->get();

        $totalsByLangue = $rows
            ->groupBy(fn ($row) => $row->langue === self::CEPE_KEY ? 'Total CEPE' : $row->langue)
            ->map(fn (Collection $group) => $group->sum('effectif'))
            ->sortDesc();

        $recapByDren = $rows
            ->groupBy('dren')
            ->map(function (Collection $drenRows, string $drenName) {
                $ciscos = $drenRows
                    ->groupBy('cisco')
                    ->map(function (Collection $ciscoRows, string $ciscoName) {
                        $centresCorrection = $ciscoRows
                            ->groupBy('centre_correction')
                            ->map(function (Collection $ccRows, string $ccName) {
                                $centresEcrit = $ccRows
                                    ->groupBy('centre_ecrit')
                                    ->map(function (Collection $ceRows, string $ceName) {
                                        return [
                                            'nom' => $ceName,
                                            'total_candidats' => $ceRows->sum('effectif'),
                                            'total_salles' => $this->countDistinctSalles($ceRows),
                                        ];
                                    })
                                    ->values();

                                return [
                                    'nom' => $ccName,
                                    'total_candidats' => $ccRows->sum('effectif'),
                                    'total_salles' => $this->countDistinctSalles($ccRows),
                                    'centres_ecrit' => $centresEcrit,
                                ];
                            })
                            ->values();

                        return [
                            'nom' => $ciscoName,
                            'total_candidats' => $ciscoRows->sum('effectif'),
                            'total_salles' => $this->countDistinctSalles($ciscoRows),
                            'centres_correction' => $centresCorrection,
                        ];
                    })
                    ->values();

                return [
                    'nom' => $drenName,
                    'total_candidats' => $drenRows->sum('effectif'),
                    'total_salles' => $this->countDistinctSalles($drenRows),
                    'ciscos' => $ciscos,
                ];
            })
            ->values();

        $recapByDrenPaginated = $this->paginateCollection(
            $recapByDren,
            (int) $request->integer('per_page', 8),
            'page_dren',
            $request
        );

        $chartData = $recapByDren->map(function (array $dren) {
            return [
                'label' => $dren['nom'],
                'value' => $dren['total_candidats'],
            ];
        });

        $bepcComparisonTotals = $rows
            ->filter(fn ($row) => (string) $row->langue !== self::CEPE_KEY)
            ->groupBy(fn ($row) => strtoupper(trim((string) $row->langue)))
            ->map(fn (Collection $group) => $group->sum('effectif'));

        $languesComparisonChart = $bepcComparisonTotals
            ->filter(fn (int $value, string $label) => ! str_contains($label, 'OPTION'))
            ->sortDesc()
            ->map(fn (int $value, string $label) => [
                'label' => $label,
                'value' => $value,
            ])
            ->values();

        $optionBTotal = (int) $bepcComparisonTotals
            ->filter(fn (int $value, string $label) => str_contains($label, 'OPTION B'))
            ->sum();
        $optionATotal = (int) $bepcComparisonTotals
            ->filter(fn (int $value, string $label) => ! str_contains($label, 'OPTION B'))
            ->sum();
        $optionsComparisonChart = collect([
            ['label' => 'OPTION A', 'value' => $optionATotal],
            ['label' => 'OPTION B', 'value' => $optionBTotal],
        ]);
        $activeRows = $rows->filter(fn ($row) => (int) $row->effectif > 0);

        return view('repartition.dashboard', [
            'rows' => $rows,
            'filters' => $filters,
            'annees' => $annees,
            'drens' => $drens,
            'ciscos' => $ciscos,
            'ciscosByDren' => $ciscosByDren,
            'specialCandidates' => $specialCandidates,
            'totalsByLangue' => $totalsByLangue,
            'languesComparisonChart' => $languesComparisonChart,
            'optionsComparisonChart' => $optionsComparisonChart,
            'showLangueComparison' => $filters['type_examen'] !== self::TYPE_CEPE,
            'recapByDren' => $recapByDrenPaginated,
            'drenChartData' => $chartData,
            'centresSaisieStats' => $centresSaisieStats,
            'globalStats' => [
                'total_candidats' => $rows->sum('effectif'),
                'total_salles' => $this->countDistinctSalles($rows),
                'total_drens' => $rows->pluck('dren')->unique()->count(),
                'total_ciscos' => $rows->pluck('cisco')->unique()->count(),
                // 'total_centres_correction' => $rows->pluck('centre_correction')->unique()->count(),
                // 'total_centres_correction' => $rows->unique(fn ($row) => $row->centre_correction . '|' . $row->cisco)->count(),
                // 'total_centres_ecrit' => $rows->pluck('centre_ecrit')->unique()->count(),
                
                'total_centres_correction' => $activeRows
                    ->unique(fn ($row) => 
                        strtolower(trim($row->centre_correction)) . '|' . strtolower(trim($row->cisco))
                    )
                    ->count(),
                
                'total_centres_ecrit' => $rows
                            ->filter(fn ($row) => (int) $row->effectif > 0)
                            ->unique(fn ($row) => 
                                strtolower(trim($row->centre_ecrit)) . '|' . strtolower(trim($row->cisco))
                            )
                            ->count(),
                
            ],
        ]);
    }

    public function updateSpecialCandidate(Request $request, int $candidate): RedirectResponse
    {
        $validated = $request->validate([
            'numero_salle' => ['required', 'integer', 'min:1'],
            'type_handicap' => ['required', 'string', 'max:255'],
        ]);

        $specialCandidate = DB::table('repartition_salles_specifiques')
            ->where('id', $candidate)
            ->first();

        if (! $specialCandidate) {
            return back()->withErrors(['candidate' => 'Candidat à besoins spécifiques introuvable.']);
        }

        $roomExists = DB::table('repartition_salles')
            ->where('centre_ecrit_id', $specialCandidate->centre_ecrit_id)
            ->where('annee', $specialCandidate->annee)
            ->where('numero_salle', (int) $validated['numero_salle'])
            ->when(
                $specialCandidate->type_examen === self::TYPE_BEPC,
                fn ($query) => $query->where('langue', '!=', self::CEPE_KEY)
            )
            ->when(
                $specialCandidate->type_examen === self::TYPE_CEPE,
                fn ($query) => $query->where('langue', self::CEPE_KEY)
            )
            ->exists();

        if (! $roomExists) {
            return back()->withErrors([
                'numero_salle' => 'La salle choisie n’existe pas dans la répartition de ce centre.',
            ])->withInput();
        }

        $typeHandicap = trim((string) $validated['type_handicap']);
        $alreadyExists = DB::table('repartition_salles_specifiques')
            ->where('centre_ecrit_id', $specialCandidate->centre_ecrit_id)
            ->where('annee', $specialCandidate->annee)
            ->where('type_examen', $specialCandidate->type_examen)
            ->where('numero_salle', (int) $validated['numero_salle'])
            ->where('type_handicap', $typeHandicap)
            ->where('id', '!=', $candidate)
            ->exists();

        if ($alreadyExists) {
            return back()->withErrors([
                'type_handicap' => 'Une déclaration identique existe déjà pour cette salle.',
            ])->withInput();
        }

        DB::table('repartition_salles_specifiques')
            ->where('id', $candidate)
            ->update([
                'numero_salle' => (int) $validated['numero_salle'],
                'type_handicap' => $typeHandicap,
                'saisi_par' => trim((string) ($request->user()?->name ?? $specialCandidate->saisi_par ?? '')),
                'updated_at' => now(),
            ]);

        AuditLog::record($request, 'modification_besoins_specifiques', [
            'candidate_id' => $candidate,
            'centre_ecrit_id' => (int) $specialCandidate->centre_ecrit_id,
            'annee' => $specialCandidate->annee,
            'type_examen' => $specialCandidate->type_examen,
            'numero_salle' => (int) $validated['numero_salle'],
            'type_handicap' => $typeHandicap,
        ]);

        return back()->with('status', 'Candidat à besoins spécifiques modifié.');
    }

    public function centresSaisie(Request $request)
    {
        return view('repartition.centres-saisie', $this->buildCentresSaisiePayload($request));
    }

    public function saisieRecap(Request $request)
    {
        return view('repartition.saisie-recap', $this->buildSaisieRecapPayload($request));
    }

    public function centresSaisiePdf(Request $request)
    {
        $payload = $this->buildCentresSaisiePayload($request);
        $fileName = 'centres-saisie';

        if (($payload['selectedType'] ?? '') !== '') {
            $fileName .= '-'.strtolower((string) $payload['selectedType']);
        }

        if (($payload['selectedRegion'] ?? '') !== '') {
            $regionSlug = preg_replace('/[^a-z0-9]+/i', '-', (string) $payload['selectedRegion']) ?? '';
            $regionSlug = trim($regionSlug, '-');
            if ($regionSlug !== '') {
                $fileName .= '-'.strtolower($regionSlug);
            }
        }

        $pdf = Pdf::loadView('repartition.centres-saisie-pdf', $payload)
            ->setPaper('a4', 'portrait');

        return $pdf->download($fileName.'.pdf');
    }

    public function livrePreview(Request $request)
    {
        $requestedType = strtoupper((string) $request->query('type_examen', self::TYPE_ALL));
        if (! in_array($requestedType, [self::TYPE_ALL, self::TYPE_BEPC, self::TYPE_CEPE], true)) {
            $requestedType = self::TYPE_ALL;
            $request->merge(['type_examen' => $requestedType]);
        }

        $bookVariant = (string) $request->query('variant', 'default');
        $isBepcLanguesVariant = $requestedType === self::TYPE_BEPC && $bookVariant === 'langues';

        [$rows, $filters, $annees, $drens, $ciscos] = $this->getFilteredRows($request);
        $bookData = $this->buildBookData($rows, [
            'bepc_langues_only' => $isBepcLanguesVariant,
        ]);
        $totalGe = collect($bookData)->sum('ge_count');
        $totalPe = collect($bookData)->sum('pe');
        $recapSheets = $this->buildRecapSheets($bookData);
        $centrePagesByDren = $this->paginateCentresByDren($bookData);
        $specialCandidates = DB::table('repartition_salles_specifiques as rss')
            ->join('centre_ecrits as ce', 'ce.id', '=', 'rss.centre_ecrit_id')
            ->join('centre_corrections as cc', 'cc.id', '=', 'ce.centre_correction_id')
            ->join('ciscos as cs', 'cs.id', '=', 'cc.cisco_id')
            ->join('drens as d', 'd.id', '=', 'cs.dren_id')
            ->select([
                'rss.type_examen',
                'rss.annee',
                'd.nom as dren',
                'cs.nom as cisco',
                'cc.nom as centre_correction',
                'ce.nom as centre_ecrit',
                'rss.numero_salle',
                'rss.type_handicap',
                'rss.saisi_par',
            ])
            ->when($filters['annee'] !== '', fn ($query) => $query->where('rss.annee', $filters['annee']))
            ->when($filters['dren'] !== '', fn ($query) => $query->where('d.nom', $filters['dren']))
            ->when($filters['cisco'] !== '', fn ($query) => $query->where('cs.nom', $filters['cisco']))
            ->when($filters['centre_search'] !== '', fn ($query) => $query->where('ce.nom', 'like', '%'.$filters['centre_search'].'%'))
            ->when($filters['type_examen'] === self::TYPE_BEPC, fn ($query) => $query->where('rss.type_examen', self::TYPE_BEPC))
            ->when($filters['type_examen'] === self::TYPE_CEPE, fn ($query) => $query->where('rss.type_examen', self::TYPE_CEPE))
            ->orderBy('rss.annee')
            ->orderBy('d.nom')
            ->orderBy('cs.nom')
            ->orderBy('cc.nom')
            ->orderBy('ce.nom')
            ->orderBy('rss.numero_salle')
            ->get();

        $globalRows = $isBepcLanguesVariant
            ? $rows->filter(fn ($row) => in_array((string) $row->langue, ['Anglais', 'Allemand', 'Esp'], true))
            : $rows;

        return view('repartition.livre-preview', [
            'filters' => $filters,
            'annees' => $annees,
            'drens' => $drens,
            'ciscos' => $ciscos,
            'centrePagesByDren' => $centrePagesByDren,
            'recapSheets' => $recapSheets,
            'specialCandidates' => $specialCandidates,
            'globalStats' => [
                'total_candidats' => $globalRows->sum('effectif'),
                'total_salles' => $this->countDistinctSalles($globalRows),
                'total_pe' => $totalPe,
                'total_ge' => $totalGe,
                'totaux_langues' => $globalRows
                    ->groupBy(fn ($row) => $row->langue === self::CEPE_KEY ? 'Total CEPE' : $row->langue)
                    ->map(fn (Collection $group) => $group->sum('effectif'))
                    ->sortDesc(),
            ],
            'bookVariant' => $bookVariant,
            'pdfMode' => false,
        ]);
    }

    public function livrePeGe(Request $request)
    {
        return view('repartition.livre-pe-ge', $this->buildLivrePeGePayload($request));
    }

    public function statsReport(Request $request)
    {
        $payload = $this->buildStatsReportPayload($request);

        return view('repartition.stat-report', $payload);
    }

    public function statsReportPdf(Request $request)
    {
        $payload = $this->buildStatsReportPayload($request);
        $payload['pdfMode'] = true;
        $payload['pdfDownload'] = true;

        $fileName = 'rapport_statistique_'.($payload['filters']['annee_n'] !== '' ? $payload['filters']['annee_n'].'_' : '').'complet.pdf';

        $pdf = Pdf::loadView('repartition.stat-report', $payload)
            ->setPaper('a4', 'landscape')
            ->setOption(['isHtml5ParserEnabled' => true]);

        return $pdf->download($fileName);
    }

    public function statsReportWord(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $payload = $this->buildStatsReportPayload($request);
        $payload['pdfMode'] = true;
        $content = view('repartition.stat-report', $payload)->render();

        $fileName = 'rapport_statistique_'.($payload['filters']['annee_n'] !== '' ? $payload['filters']['annee_n'].'_' : '').'word.doc';

        return response($content, 200, [
            'Content-Type' => 'application/msword; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    public function statsReportWordSimple(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $payload = $this->buildStatsReportSimplePayload($request);
        $payload['pdfMode'] = true;
        $content = view('repartition.stat-report-simple', $payload)->render();

        $fileName = 'rapport_statistique_simple_'.($payload['filters']['annee'] !== '' ? $payload['filters']['annee'].'_' : '').'word.doc';

        return response($content, 200, [
            'Content-Type' => 'application/msword; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    public function statsReportPdfSimple(Request $request)
    {
        $payload = $this->buildStatsReportSimplePayload($request);
        $payload['pdfMode'] = true;

        $fileName = 'rapport_statistique_simple_'.($payload['filters']['annee'] !== '' ? $payload['filters']['annee'].'_' : '').'pdf';

        $pdf = Pdf::loadView('repartition.stat-report-simple', $payload)
            ->setPaper('a4', 'landscape')
            ->setOption(['isHtml5ParserEnabled' => true]);

        return $pdf->download($fileName);
    }

    private function buildStatsReportPayload(Request $request): array
    {
        $anneesCurrent = DB::table('repartition_salles')
            ->select('annee')
            ->distinct()
            ->orderByDesc('annee')
            ->pluck('annee')
            ->values()
            ->all();

        $anneesImport = DB::table('repartition_stats_imports')
            ->select('annee')
            ->distinct()
            ->orderByDesc('annee')
            ->pluck('annee')
            ->values()
            ->all();
        $anneesImportDren = DB::table('repartition_stats_dren_imports')
            ->select('annee')
            ->distinct()
            ->orderByDesc('annee')
            ->pluck('annee')
            ->values()
            ->all();
        $drenImportHasType = Schema::hasColumn('repartition_stats_dren_imports', 'type_examen');

        $filters = [
            'annee_n' => (string) $request->query('annee_n', $anneesCurrent[0] ?? ''),
            'annee_n1' => (string) $request->query('annee_n1', $anneesImport[0] ?? ''),
            'annee_n1_dren' => (string) $request->query('annee_n1_dren', $anneesImportDren[0] ?? ''),
            'type_examen' => strtoupper((string) $request->query('type_examen', self::TYPE_ALL)),
            'dren' => (string) $request->query('dren', ''),
            'cisco' => (string) $request->query('cisco', ''),
        ];

        if (! in_array($filters['type_examen'], [self::TYPE_ALL, self::TYPE_BEPC, self::TYPE_CEPE], true)) {
            $filters['type_examen'] = self::TYPE_ALL;
        }

        $drens = DB::table('drens')
            ->select('nom')
            ->orderBy('nom')
            ->pluck('nom')
            ->toArray();

        $ciscosQuery = DB::table('ciscos as cs')
            ->join('drens as d', 'd.id', '=', 'cs.dren_id')
            ->select('cs.nom')
            ->orderBy('cs.nom');

        if ($filters['dren'] !== '') {
            $ciscosQuery->where('d.nom', $filters['dren']);
        }

        $ciscos = $ciscosQuery->pluck('cs.nom')->toArray();

        $ciscosByDren = DB::table('ciscos as cs')
            ->join('drens as d', 'd.id', '=', 'cs.dren_id')
            ->orderBy('d.nom')
            ->orderBy('cs.nom')
            ->get(['d.nom as dren', 'cs.nom as cisco'])
            ->groupBy('dren')
            ->map(fn (Collection $items) => $items->pluck('cisco')->values()->all())
            ->toArray();

        $rows = collect();
        if ($filters['annee_n'] !== '') {
            $query = DB::table('repartition_salles as rs')
                ->join('centre_ecrits as ce', 'ce.id', '=', 'rs.centre_ecrit_id')
                ->join('centre_corrections as cc', 'cc.id', '=', 'ce.centre_correction_id')
                ->join('ciscos as cs', 'cs.id', '=', 'cc.cisco_id')
                ->join('drens as d', 'd.id', '=', 'cs.dren_id')
                ->select([
                    'rs.annee',
                    'rs.langue',
                    'rs.numero_salle',
                    'rs.effectif',
                    'ce.id as centre_ecrit_id',
                    'ce.nom as centre_ecrit',
                    'cc.nom as centre_correction',
                    'cs.nom as cisco',
                    'd.nom as dren',
                ])
                ->where('rs.annee', $filters['annee_n'])
                ->orderBy('d.nom')
                ->orderBy('cs.nom')
                ->orderBy('cc.nom')
                ->orderBy('ce.nom')
                ->orderBy('rs.numero_salle');

            if ($filters['dren'] !== '') {
                $query->where('d.nom', $filters['dren']);
            }
            if ($filters['cisco'] !== '') {
                $query->where('cs.nom', $filters['cisco']);
            }
            if ($filters['type_examen'] === self::TYPE_BEPC) {
                $query->where('rs.langue', '!=', self::CEPE_KEY);
            } elseif ($filters['type_examen'] === self::TYPE_CEPE) {
                $query->where('rs.langue', self::CEPE_KEY);
            }

            $rows = $query->get()->map(function ($row) {
                $row->type_examen = $row->langue === self::CEPE_KEY ? self::TYPE_CEPE : self::TYPE_BEPC;

                return $row;
            });

            $rows = $this->filterOutEmptySalles($rows);
        }

        $currentCentres = $rows
            ->groupBy(fn ($row) => $this->buildCentreKey(
                (string) $row->type_examen,
                (string) $row->dren,
                (string) $row->cisco,
                (string) $row->centre_correction,
                (string) $row->centre_ecrit
            ))
            ->map(function (Collection $group) {
                $first = $group->first();

                return [
                    'type_examen' => (string) $first->type_examen,
                    'dren' => (string) $first->dren,
                    'cisco' => (string) $first->cisco,
                    'centre_correction' => (string) $first->centre_correction,
                    'centre_ecrit' => (string) $first->centre_ecrit,
                    'total_candidats' => (int) $group->sum('effectif'),
                    'total_salles' => $this->countDistinctSalles($group),
                ];
            });

        $previousRowsQuery = DB::table('repartition_stats_imports')
            ->orderBy('dren')
            ->orderBy('cisco')
            ->orderBy('centre_correction')
            ->orderBy('centre_ecrit');

        if ($filters['annee_n1'] !== '') {
            $previousRowsQuery->where('annee', $filters['annee_n1']);
        }
        if ($filters['dren'] !== '') {
            $previousRowsQuery->where('dren', $filters['dren']);
        }
        if ($filters['cisco'] !== '') {
            $previousRowsQuery->where('cisco', $filters['cisco']);
        }
        if ($filters['type_examen'] === self::TYPE_BEPC) {
            $previousRowsQuery->where('type_examen', self::TYPE_BEPC);
        } elseif ($filters['type_examen'] === self::TYPE_CEPE) {
            $previousRowsQuery->where('type_examen', self::TYPE_CEPE);
        }

        $previousRows = $previousRowsQuery->get();
        $previousCentres = collect($previousRows)->keyBy(function ($row) {
            return $this->buildCentreKey(
                (string) $row->type_examen,
                (string) $row->dren,
                (string) $row->cisco,
                (string) $row->centre_correction,
                (string) $row->centre_ecrit
            );
        });

        $comparisonRows = collect(array_unique(array_merge(
            $currentCentres->keys()->all(),
            $previousCentres->keys()->all()
        )))->map(function (string $key) use ($currentCentres, $previousCentres) {
            $current = $currentCentres->get($key);
            $previous = $previousCentres->get($key);

            $currentCandidates = (int) ($current['total_candidats'] ?? 0);
            $previousCandidates = (int) ($previous->total_candidats ?? 0);
            $currentSalles = (int) ($current['total_salles'] ?? 0);
            $previousSalles = (int) ($previous->total_salles ?? 0);

            $diffCandidates = $currentCandidates - $previousCandidates;
            $diffSalles = $currentSalles - $previousSalles;
            $progressCandidates = $previousCandidates > 0
                ? round(($diffCandidates / $previousCandidates) * 100, 1)
                : ($currentCandidates > 0 ? 100.0 : 0.0);
            $progressSalles = $previousSalles > 0
                ? round(($diffSalles / $previousSalles) * 100, 1)
                : ($currentSalles > 0 ? 100.0 : 0.0);

            $status = '';
            if ($current && ! $previous) {
                $status = 'Nouveau centre';
            } elseif (! $current && $previous) {
                $status = "Centre n'existe plus";
            }

            return [
                'type_examen' => (string) ($current['type_examen'] ?? $previous?->type_examen ?? ''),
                'dren' => (string) ($current['dren'] ?? $previous?->dren ?? ''),
                'cisco' => (string) ($current['cisco'] ?? $previous?->cisco ?? ''),
                'centre_correction' => (string) ($current['centre_correction'] ?? $previous?->centre_correction ?? ''),
                'centre_ecrit' => (string) ($current['centre_ecrit'] ?? $previous?->centre_ecrit ?? ''),
                'current_candidats' => $currentCandidates,
                'previous_candidats' => $previousCandidates,
                'current_salles' => $currentSalles,
                'previous_salles' => $previousSalles,
                'ecart_candidats' => $diffCandidates,
                'ecart_salles' => $diffSalles,
                'progression_candidats' => $progressCandidates,
                'progression_salles' => $progressSalles,
                'status' => $status,
            ];
        })->sortBy(['dren', 'cisco', 'centre_correction', 'centre_ecrit'])->values();

        $previousCiscoRows = DB::table('repartition_stats_dren_imports as r')
            ->leftJoin('ciscos as cs', 'cs.nom', '=', 'r.cisco')
            ->leftJoin('drens as d', 'd.id', '=', 'cs.dren_id')
            ->select([
                'r.cisco',
                'd.nom as dren',
                'r.total_candidats',
                'r.total_salles',
            ])
            ->when($filters['annee_n1_dren'] !== '', fn ($query) => $query->where('r.annee', $filters['annee_n1_dren']))
            ->when($filters['dren'] !== '', fn ($query) => $query->where('d.nom', $filters['dren']))
            ->when($filters['cisco'] !== '', fn ($query) => $query->where('r.cisco', $filters['cisco']))
            ->when($filters['type_examen'] !== self::TYPE_ALL && $drenImportHasType, fn ($query) => $query->where('r.type_examen', $filters['type_examen']))
            ->get();

        $bookData = $rows->isNotEmpty() ? $this->buildBookData($rows) : [];

        $handicapCounts = DB::table('repartition_salles_specifiques as rss')
            ->join('centre_ecrits as ce', 'ce.id', '=', 'rss.centre_ecrit_id')
            ->join('centre_corrections as cc', 'cc.id', '=', 'ce.centre_correction_id')
            ->join('ciscos as cs', 'cs.id', '=', 'cc.cisco_id')
            ->join('drens as d', 'd.id', '=', 'cs.dren_id')
            ->select([
                'd.nom as dren',
                'cs.nom as cisco',
                DB::raw('count(*) as total'),
            ])
            ->when($filters['annee_n'] !== '', fn ($query) => $query->where('rss.annee', $filters['annee_n']))
            ->when($filters['dren'] !== '', fn ($query) => $query->where('d.nom', $filters['dren']))
            ->when($filters['cisco'] !== '', fn ($query) => $query->where('cs.nom', $filters['cisco']))
            ->when($filters['type_examen'] === self::TYPE_BEPC, fn ($query) => $query->where('rss.type_examen', self::TYPE_BEPC))
            ->when($filters['type_examen'] === self::TYPE_CEPE, fn ($query) => $query->where('rss.type_examen', self::TYPE_CEPE))
            ->groupBy('d.nom', 'cs.nom')
            ->get()
            ->groupBy('dren')
            ->map(fn (Collection $rows) => $rows->keyBy('cisco')->map(fn ($row) => (int) $row->total)->toArray())
            ->toArray();

        $recapByDren = $currentCentres
            ->groupBy('dren')
            ->map(function (Collection $centres, string $dren) use ($handicapCounts) {
                $centreCorrectionCount = $centres->pluck('centre_correction')->unique()->count();
                $centreEcritCount = $centres->pluck('centre_ecrit')->unique()->count();
                $handicapTotal = array_sum($handicapCounts[$dren] ?? []);

                return [
                    'dren' => $dren,
                    'total_candidats' => (int) $centres->sum('total_candidats'),
                    'total_salles' => (int) $centres->sum('total_salles'),
                    'total_correction' => $centreCorrectionCount,
                    'total_ecrit' => $centreEcritCount,
                    'total_handicap' => $handicapTotal,
                ];
            })
            ->values();

        $recapByCisco = $currentCentres
            ->groupBy(fn (array $centre) => $centre['dren'].'|'.$centre['cisco'])
            ->map(function (Collection $centres) use ($handicapCounts) {
                $first = $centres->first();
                $dren = (string) ($first['dren'] ?? '');
                $cisco = (string) ($first['cisco'] ?? '');
                $centreCorrectionCount = $centres->pluck('centre_correction')->unique()->count();
                $centreEcritCount = $centres->pluck('centre_ecrit')->unique()->count();
                $handicapTotal = (int) ($handicapCounts[$dren][$cisco] ?? 0);

                return [
                    'dren' => $dren,
                    'cisco' => $cisco,
                    'total_candidats' => (int) $centres->sum('total_candidats'),
                    'total_salles' => (int) $centres->sum('total_salles'),
                    'total_correction' => $centreCorrectionCount,
                    'total_ecrit' => $centreEcritCount,
                    'total_handicap' => $handicapTotal,
                ];
            })
            ->sortBy(['dren', 'cisco'])
            ->values();

        $peGeByDren = collect($bookData)
            ->groupBy('dren')
            ->map(function (Collection $centres, string $dren) {
                return [
                    'dren' => $dren,
                    'total_pe' => (int) $centres->sum('pe'),
                    'total_ge' => (int) $centres->sum('ge_count'),
                ];
            })
            ->values();

        $bepcLangueTotals = $rows
            ->filter(fn ($row) => (string) $row->langue !== self::CEPE_KEY)
            ->groupBy(fn ($row) => strtoupper(trim((string) $row->langue)))
            ->map(fn (Collection $group) => $group->sum('effectif'))
            ->sortDesc();

        $languesComparisonChart = $bepcLangueTotals
            ->filter(fn (int $value, string $label) => ! str_contains($label, 'OPTION'))
            ->map(fn (int $value, string $label) => [
                'label' => $label,
                'value' => $value,
            ])
            ->values();

        $optionBTotal = (int) $bepcLangueTotals
            ->filter(fn (int $value, string $label) => str_contains($label, 'OPTION B'))
            ->sum();
        $optionATotal = (int) $bepcLangueTotals
            ->filter(fn (int $value, string $label) => ! str_contains($label, 'OPTION B'))
            ->sum();

        $optionsComparisonChart = collect([
            ['label' => 'OPTION A', 'value' => $optionATotal],
            ['label' => 'OPTION B', 'value' => $optionBTotal],
        ]);

        $previousCiscoTotals = collect($previousCiscoRows)
            ->groupBy(fn ($row) => $row->dren.'|'.$row->cisco)
            ->map(function (Collection $group) {
                $first = $group->first();
                return [
                    'dren' => (string) $first->dren,
                    'cisco' => (string) $first->cisco,
                    'total_candidats' => (int) $group->sum('total_candidats'),
                    'total_salles' => (int) $group->sum('total_salles'),
                ];
            });

        $ciscoComparison = collect(array_unique(array_merge(
            $recapByCisco->map(fn (array $row) => $row['dren'].'|'.$row['cisco'])->all(),
            $previousCiscoTotals->keys()->all()
        )))->map(function (string $key) use ($recapByCisco, $previousCiscoTotals) {
            [$dren, $cisco] = array_pad(explode('|', $key, 2), 2, '');
            $current = $recapByCisco->first(fn (array $row) => $row['dren'] === $dren && $row['cisco'] === $cisco);
            $previous = $previousCiscoTotals->get($key);
            $currentC = (int) ($current['total_candidats'] ?? 0);
            $previousC = (int) ($previous['total_candidats'] ?? 0);
            $currentS = (int) ($current['total_salles'] ?? 0);
            $previousS = (int) ($previous['total_salles'] ?? 0);

            return [
                'dren' => (string) $dren,
                'cisco' => (string) $cisco,
                'current_candidats' => $currentC,
                'previous_candidats' => $previousC,
                'current_salles' => $currentS,
                'previous_salles' => $previousS,
                'ecart_candidats' => $currentC - $previousC,
                'ecart_salles' => $currentS - $previousS,
            ];
        })->sortBy(['dren', 'cisco'])->values();

        $currentDrenLangues = $rows
            ->filter(fn ($row) => (string) $row->langue !== self::CEPE_KEY)
            ->groupBy('dren')
            ->map(function (Collection $group) {
                $bucket = [
                    'anglais' => 0,
                    'espagnol' => 0,
                    'allemand' => 0,
                    'option_b' => 0,
                ];
                foreach ($group as $row) {
                    $label = strtoupper(trim((string) $row->langue));
                    if (str_contains($label, 'OPTION B')) {
                        $bucket['option_b'] += (int) $row->effectif;
                    } elseif (str_contains($label, 'ANGLAIS')) {
                        $bucket['anglais'] += (int) $row->effectif;
                    } elseif (str_contains($label, 'ESP')) {
                        $bucket['espagnol'] += (int) $row->effectif;
                    } elseif (str_contains($label, 'ALL')) {
                        $bucket['allemand'] += (int) $row->effectif;
                    }
                }
                return $bucket;
            })
            ->toArray();

        // previous imports are stored per Cisco (column 'cisco'), join to ciscos/drens to know DREN for each Cisco
        $drenImportQuery = DB::table('repartition_stats_dren_imports as r')
            ->leftJoin('ciscos as cs', 'cs.nom', '=', 'r.cisco')
            ->leftJoin('drens as d', 'd.id', '=', 'cs.dren_id')
            ->select('r.*', 'cs.nom as cisco', 'd.nom as dren')
            ->orderBy('d.nom');
        if ($filters['annee_n1_dren'] !== '') {
            $drenImportQuery->where('r.annee', $filters['annee_n1_dren']);
        }
        if ($filters['dren'] !== '') {
            $drenImportQuery->where('d.nom', $filters['dren']);
        }
        if ($filters['type_examen'] !== self::TYPE_ALL && $drenImportHasType) {
            $drenImportQuery->where('r.type_examen', $filters['type_examen']);
        }
        $previousDrenRows = $drenImportQuery->get()->keyBy('dren');

        $drenComparison = collect(array_unique(array_merge(
            $recapByDren->pluck('dren')->all(),
            $previousDrenRows->keys()->all()
        )))->map(function (string $dren) use ($recapByDren, $previousDrenRows, $currentDrenLangues) {
            $current = $recapByDren->first(fn (array $row) => $row['dren'] === $dren);
            $previous = $previousDrenRows->get($dren);
            $currentC = (int) ($current['total_candidats'] ?? 0);
            $previousC = (int) ($previous->total_candidats ?? 0);
            $currentS = (int) ($current['total_salles'] ?? 0);
            $previousS = (int) ($previous->total_salles ?? 0);
            $currentLang = $currentDrenLangues[$dren] ?? ['anglais' => 0, 'espagnol' => 0, 'allemand' => 0, 'option_b' => 0];

            return [
                'dren' => $dren,
                'current_candidats' => $currentC,
                'previous_candidats' => $previousC,
                'current_salles' => $currentS,
                'previous_salles' => $previousS,
                'ecart_candidats' => $currentC - $previousC,
                'ecart_salles' => $currentS - $previousS,
                'current_anglais' => (int) ($currentLang['anglais'] ?? 0),
                'current_espagnol' => (int) ($currentLang['espagnol'] ?? 0),
                'current_allemand' => (int) ($currentLang['allemand'] ?? 0),
                'current_option_b' => (int) ($currentLang['option_b'] ?? 0),
                'previous_anglais' => (int) ($previous->anglais ?? 0),
                'previous_espagnol' => (int) ($previous->espagnol ?? 0),
                'previous_allemand' => (int) ($previous->allemand ?? 0),
                'previous_option_b' => (int) ($previous->option_b ?? 0),
            ];
        })->sortBy('dren')->values();

        $diffByDren = $comparisonRows
            ->groupBy('dren')
            ->map(function (Collection $rows, string $dren) {
                return [
                    'label' => $dren,
                    'value' => (int) $rows->sum('ecart_candidats'),
                ];
            })
            ->values()
            ->sortByDesc('value')
            ->values();

        return [
            'filters' => $filters,
            'anneesCurrent' => $anneesCurrent,
            'anneesImport' => $anneesImport,
            'anneesImportDren' => $anneesImportDren,
            'drens' => $drens,
            'ciscos' => $ciscos,
            'ciscosByDren' => $ciscosByDren,
            'comparisonRows' => $comparisonRows,
            'recapByDren' => $recapByDren,
            'recapByCisco' => $recapByCisco,
            'drenComparison' => $drenComparison,
            'ciscoComparison' => $ciscoComparison,
            'peGeByDren' => $peGeByDren,
            'languesComparisonChart' => $languesComparisonChart,
            'optionsComparisonChart' => $optionsComparisonChart,
            'diffByDrenChart' => $diffByDren,
            'showLangueComparison' => $filters['type_examen'] !== self::TYPE_CEPE,
            'globalStats' => [
                'current_candidats' => (int) $currentCentres->sum('total_candidats'),
                'current_salles' => (int) $currentCentres->sum('total_salles'),
                'previous_candidats' => (int) $previousRows->sum('total_candidats'),
                'previous_salles' => (int) $previousRows->sum('total_salles'),
                'total_handicap' => (int) array_sum(array_map('array_sum', $handicapCounts)),
            ],
            'pdfMode' => false,
        ];
    }

    private function buildStatsReportSimplePayload(Request $request): array
    {
        $anneesCurrent = DB::table('repartition_salles')
            ->select('annee')
            ->distinct()
            ->orderByDesc('annee')
            ->pluck('annee')
            ->values()
            ->all();

        $filters = [
            'annee' => (string) $request->query('annee', $anneesCurrent[0] ?? ''),
            'type_examen' => strtoupper((string) $request->query('type_examen', self::TYPE_ALL)),
            'dren' => (string) $request->query('dren', ''),
            'cisco' => (string) $request->query('cisco', ''),
        ];

        if (! in_array($filters['type_examen'], [self::TYPE_ALL, self::TYPE_BEPC, self::TYPE_CEPE], true)) {
            $filters['type_examen'] = self::TYPE_ALL;
        }

        $drens = DB::table('drens')
            ->select('nom')
            ->orderBy('nom')
            ->pluck('nom')
            ->toArray();

        $ciscosQuery = DB::table('ciscos as cs')
            ->join('drens as d', 'd.id', '=', 'cs.dren_id')
            ->select('cs.nom')
            ->orderBy('cs.nom');

        if ($filters['dren'] !== '') {
            $ciscosQuery->where('d.nom', $filters['dren']);
        }

        $ciscos = $ciscosQuery->pluck('cs.nom')->toArray();

        $rows = collect();
        if ($filters['annee'] !== '') {
            $query = DB::table('repartition_salles as rs')
                ->join('centre_ecrits as ce', 'ce.id', '=', 'rs.centre_ecrit_id')
                ->join('centre_corrections as cc', 'cc.id', '=', 'ce.centre_correction_id')
                ->join('ciscos as cs', 'cs.id', '=', 'cc.cisco_id')
                ->join('drens as d', 'd.id', '=', 'cs.dren_id')
                ->select([
                    'rs.annee',
                    'rs.langue',
                    'rs.numero_salle',
                    'rs.effectif',
                    'ce.id as centre_ecrit_id',
                    'ce.nom as centre_ecrit',
                    'cc.nom as centre_correction',
                    'cs.nom as cisco',
                    'd.nom as dren',
                ])
                ->where('rs.annee', $filters['annee'])
                ->orderBy('d.nom')
                ->orderBy('cs.nom')
                ->orderBy('cc.nom')
                ->orderBy('ce.nom')
                ->orderBy('rs.numero_salle');

            if ($filters['dren'] !== '') {
                $query->where('d.nom', $filters['dren']);
            }
            if ($filters['cisco'] !== '') {
                $query->where('cs.nom', $filters['cisco']);
            }
            if ($filters['type_examen'] === self::TYPE_BEPC) {
                $query->where('rs.langue', '!=', self::CEPE_KEY);
            } elseif ($filters['type_examen'] === self::TYPE_CEPE) {
                $query->where('rs.langue', self::CEPE_KEY);
            }

            $rows = $query->get()->map(function ($row) {
                $row->type_examen = $row->langue === self::CEPE_KEY ? self::TYPE_CEPE : self::TYPE_BEPC;
                return $row;
            });

            $rows = $this->filterOutEmptySalles($rows);
        }

        $currentCentres = $rows
            ->groupBy(fn ($row) => $this->buildCentreKey(
                (string) $row->type_examen,
                (string) $row->dren,
                (string) $row->cisco,
                (string) $row->centre_correction,
                (string) $row->centre_ecrit
            ))
            ->map(function (Collection $group) {
                $first = $group->first();
                return [
                    'type_examen' => (string) $first->type_examen,
                    'dren' => (string) $first->dren,
                    'cisco' => (string) $first->cisco,
                    'centre_correction' => (string) $first->centre_correction,
                    'centre_ecrit' => (string) $first->centre_ecrit,
                    'total_candidats' => (int) $group->sum('effectif'),
                    'total_salles' => $this->countDistinctSalles($group),
                ];
            });

        // Récap général
        $totalCentresCorrection = $currentCentres->pluck('centre_correction')->unique()->count();
        $totalCentresEcrit = $currentCentres->pluck('centre_ecrit')->unique()->count();
        $totalSalles = $currentCentres->sum('total_salles');
        $totalCandidats = $currentCentres->sum('total_candidats');

        // Récap par région (DREN)
        $recapByDren = $currentCentres
            ->groupBy('dren')
            ->map(function (Collection $centres, string $dren) {
                return [
                    'dren' => $dren,
                    'total_candidats' => (int) $centres->sum('total_candidats'),
                    'total_salles' => (int) $centres->sum('total_salles'),
                    'total_correction' => $centres->pluck('centre_correction')->unique()->count(),
                    'total_ecrit' => $centres->pluck('centre_ecrit')->unique()->count(),
                ];
            })
            ->values();

        // Candidats handicapés
        $handicapRows = DB::table('repartition_salles_specifiques as rss')
            ->join('centre_ecrits as ce', 'ce.id', '=', 'rss.centre_ecrit_id')
            ->join('centre_corrections as cc', 'cc.id', '=', 'ce.centre_correction_id')
            ->join('ciscos as cs', 'cs.id', '=', 'cc.cisco_id')
            ->join('drens as d', 'd.id', '=', 'cs.dren_id')
            ->select([
                'd.nom as dren',
                'cs.nom as cisco',
                'cc.nom as centre_correction',
                'ce.nom as centre_ecrit',
                'rss.type_handicap',
                'rss.numero_salle',
            ])
            ->where('rss.annee', $filters['annee'])
            ->when($filters['dren'] !== '', fn ($query) => $query->where('d.nom', $filters['dren']))
            ->when($filters['cisco'] !== '', fn ($query) => $query->where('cs.nom', $filters['cisco']))
            ->when($filters['type_examen'] === self::TYPE_BEPC, fn ($query) => $query->where('rss.type_examen', self::TYPE_BEPC))
            ->when($filters['type_examen'] === self::TYPE_CEPE, fn ($query) => $query->where('rss.type_examen', self::TYPE_CEPE))
            ->orderBy('d.nom')
            ->orderBy('cs.nom')
            ->orderBy('cc.nom')
            ->orderBy('ce.nom')
            ->get();

        $handicapByType = $handicapRows->groupBy('type_handicap')->map->count();
        $handicapByDren = $handicapRows->groupBy('dren')->map->count();

        // Comparaison langues pour BEPC
        $languesComparison = [];
        if ($filters['type_examen'] !== self::TYPE_CEPE) {
            $languesComparison = $rows
                ->where('langue', '!=', self::CEPE_KEY)
                ->groupBy('langue')
                ->map(function (Collection $langueRows, string $langue) {
                    return [
                        'langue' => $langue,
                        'total_candidats' => (int) $langueRows->sum('effectif'),
                        'total_salles' => $this->countDistinctSalles($langueRows),
                    ];
                })
                ->sortByDesc('total_candidats')
                ->values();
        }

        return [
            'filters' => $filters,
            'anneesCurrent' => $anneesCurrent,
            'drens' => $drens,
            'ciscos' => $ciscos,
            'recapGeneral' => [
                'total_centres_correction' => $totalCentresCorrection,
                'total_centres_ecrit' => $totalCentresEcrit,
                'total_salles' => $totalSalles,
                'total_candidats' => $totalCandidats,
            ],
            'recapByDren' => $recapByDren,
            'handicapByType' => $handicapByType,
            'handicapByDren' => $handicapByDren,
            'languesComparison' => $languesComparison,
            'showLangueComparison' => $filters['type_examen'] !== self::TYPE_CEPE,
        ];
    }

    public function importPreviousStats(Request $request): RedirectResponse
    {
        $request->validate([
            'stats_file' => ['required', 'file'],
            'annee_import' => ['nullable', 'string', 'max:9'],
            'type_examen_import' => ['nullable', 'string', 'max:10'],
        ]);

        $rows = $this->parseImportCsvRows($request, 'stats_file');
        if ($rows === []) {
            return back()->withErrors(['stats_file' => 'Fichier vide ou format invalide.']);
        }

        $importYear = trim((string) $request->input('annee_import', ''));
        $importType = strtoupper(trim((string) $request->input('type_examen_import', '')));
        if (! in_array($importType, [self::TYPE_BEPC, self::TYPE_CEPE], true)) {
            $importType = '';
        }

        $created = 0;
        $updated = 0;
        $errors = 0;
        $rejects = [];

        foreach ($rows as $idx => $row) {
            $lineNumber = $idx + 2;
            $annee = trim((string) ($row['annee'] ?? $importYear));
            $typeExamen = strtoupper(trim((string) ($row['type_examen'] ?? $importType)));
            $dren = trim((string) ($row['dren'] ?? ''));
            $cisco = trim((string) ($row['cisco'] ?? ''));
            $centreCorrection = trim((string) ($row['centre_correction'] ?? ''));
            $centreEcrit = trim((string) ($row['centre_ecrit'] ?? ''));
            $totalSalles = $row['total_salles'] ?? null;
            $totalCandidats = $row['total_candidats'] ?? null;

            if ($annee === '' || $typeExamen === '' || $dren === '' || $cisco === '' || $centreCorrection === '' || $centreEcrit === '') {
                $rejects[] = "Ligne {$lineNumber}: colonnes obligatoires manquantes.";
                $errors++;
                continue;
            }
            if (! in_array($typeExamen, [self::TYPE_BEPC, self::TYPE_CEPE], true)) {
                $rejects[] = "Ligne {$lineNumber}: type d'examen invalide ({$typeExamen}).";
                $errors++;
                continue;
            }
            if ($totalSalles === null || $totalCandidats === null || ! is_numeric($totalSalles) || ! is_numeric($totalCandidats)) {
                $rejects[] = "Ligne {$lineNumber}: total salles/candidats invalide.";
                $errors++;
                continue;
            }

            $totalSalles = max(0, (int) $totalSalles);
            $totalCandidats = max(0, (int) $totalCandidats);

            $centreKey = $this->buildCentreKeyHash($typeExamen, $dren, $cisco, $centreCorrection, $centreEcrit);

            $exists = DB::table('repartition_stats_imports')
                ->where('annee', $annee)
                ->where('type_examen', $typeExamen)
                ->where('centre_key', $centreKey)
                ->exists();

            if ($exists) {
                DB::table('repartition_stats_imports')
                    ->where('annee', $annee)
                    ->where('type_examen', $typeExamen)
                    ->where('centre_key', $centreKey)
                    ->update([
                        'total_salles' => $totalSalles,
                        'total_candidats' => $totalCandidats,
                        'dren' => $dren,
                        'cisco' => $cisco,
                        'centre_correction' => $centreCorrection,
                        'centre_ecrit' => $centreEcrit,
                        'updated_at' => now(),
                    ]);
                $updated++;
            } else {
                DB::table('repartition_stats_imports')->insert([
                    'annee' => $annee,
                    'type_examen' => $typeExamen,
                    'centre_key' => $centreKey,
                    'dren' => $dren,
                    'cisco' => $cisco,
                    'centre_correction' => $centreCorrection,
                    'centre_ecrit' => $centreEcrit,
                    'total_salles' => $totalSalles,
                    'total_candidats' => $totalCandidats,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $created++;
            }
        }

        $response = back()
            ->with('status', "Import stats N-1 terminé: {$created} créé(s), {$updated} mis à jour, {$errors} rejeté(s).")
            ->with('import_rejects', array_slice($rejects, 0, 120));
        AuditLog::record($request, 'import_stats_n1', [
            'created' => $created,
            'updated' => $updated,
            'errors' => $errors,
        ]);
        return $response;
    }

    public function importPreviousDrenRecap(Request $request): RedirectResponse
    {
        $request->validate([
            'dren_recap_file' => ['required', 'file'],
            'annee_import_dren' => ['nullable', 'string', 'max:9'],
            'type_examen_import_dren' => ['nullable', 'string', 'in:BEPC,CEPE'],
        ]);

        $rows = $this->parseImportCsvRows($request, 'dren_recap_file');
        if ($rows === []) {
            return back()->withErrors(['dren_recap_file' => 'Fichier vide ou format invalide.']);
        }

        $importYear = trim((string) $request->input('annee_import_dren', ''));
        $importType = strtoupper(trim((string) $request->input('type_examen_import_dren', '')));
        $hasTypeExamenColumn = Schema::hasColumn('repartition_stats_dren_imports', 'type_examen');
        if ($importYear === '') {
            // Default to previous academic year based on existing repartition_salles years when not provided
            $latest = DB::table('repartition_salles')->select('annee')->orderByDesc('annee')->value('annee');
            if ($latest && str_contains($latest, '-')) {
                [$p1, $p2] = explode('-', $latest);
                $importYear = ((int) $p1 - 1) . '-' . ((int) $p2 - 1);
            } else {
                $y = (int) date('Y');
                $importYear = ($y - 1) . '-' . $y;
            }
        }
        $created = 0;
        $updated = 0;
        $errors = 0;
        $rejects = [];
        $hasDrenColumn = Schema::hasColumn('repartition_stats_dren_imports', 'dren');

        foreach ($rows as $idx => $row) {
            $lineNumber = $idx + 2;
            $annee = trim((string) ($row['annee'] ?? $importYear));
            // Now expecting CSV to provide Cisco name; DREN will be inferred from Cisco
            $ciscoName = trim((string) ($row['cisco'] ?? ''));
            $totalCandidats = $row['total_candidats'] ?? null;
            $totalSalles = $row['total_salles'] ?? null;
            $anglais = $row['anglais'] ?? 0;
            $espagnol = $row['espagnol'] ?? 0;
            $allemand = $row['allemand'] ?? 0;
            $optionB = $row['option_b'] ?? 0;

            if ($annee === '' || $ciscoName === '') {
                $rejects[] = "Ligne {$lineNumber}: colonnes annee/cisco obligatoires.";
                $errors++;
                continue;
            }

            if (! is_numeric($totalCandidats) || ! is_numeric($totalSalles)) {
                $rejects[] = "Ligne {$lineNumber}: total candidats/salles invalide.";
                $errors++;
                continue;
            }

            // Resolve Cisco exists and infer its DREN
            $ciscoRow = DB::table('ciscos')->where('nom', $ciscoName)->first();
            if (! $ciscoRow) {
                $rejects[] = "Ligne {$lineNumber}: Cisco '{$ciscoName}' introuvable.";
                $errors++;
                continue;
            }

            $drenName = DB::table('drens')->where('id', $ciscoRow->dren_id)->value('nom');
            if (! $drenName) {
                $rejects[] = "Ligne {$lineNumber}: DREN introuvable pour la Cisco '{$ciscoName}'.";
                $errors++;
                continue;
            }

            $payload = [
                'annee' => $annee,
                'cisco' => $ciscoName,
                'total_candidats' => max(0, (int) $totalCandidats),
                'total_salles' => max(0, (int) $totalSalles),
                'anglais' => max(0, (int) $anglais),
                'espagnol' => max(0, (int) $espagnol),
                'allemand' => max(0, (int) $allemand),
                'option_b' => max(0, (int) $optionB),
            ];

            if ($hasDrenColumn) {
                $payload['dren'] = $drenName;
            }
            if ($hasTypeExamenColumn && $importType !== '') {
                $payload['type_examen'] = $importType;
            }

            // Upsert by Cisco+annee+(optional exam type): if exists, add the incoming totals to the Cisco row
            $existingQuery = DB::table('repartition_stats_dren_imports')
                ->where('annee', $annee)
                ->where('cisco', $ciscoName);
            if ($hasTypeExamenColumn) {
                if ($importType !== '') {
                    $existingQuery->where('type_examen', $importType);
                } else {
                    $existingQuery->whereNull('type_examen');
                }
            }
            $existing = $existingQuery->first();

            if ($existing) {
                $updateData = [
                    'total_candidats' => ((int) $existing->total_candidats) + $payload['total_candidats'],
                    'total_salles' => ((int) $existing->total_salles) + $payload['total_salles'],
                    'anglais' => ((int) $existing->anglais) + $payload['anglais'],
                    'espagnol' => ((int) $existing->espagnol) + $payload['espagnol'],
                    'allemand' => ((int) $existing->allemand) + $payload['allemand'],
                    'option_b' => ((int) $existing->option_b) + $payload['option_b'],
                    'updated_at' => now(),
                ];

                if ($hasDrenColumn) {
                    $updateData['dren'] = $drenName;
                }
                if ($hasTypeExamenColumn && $importType !== '') {
                    $updateData['type_examen'] = $importType;
                }

                $updateQuery = DB::table('repartition_stats_dren_imports')
                    ->where('annee', $annee)
                    ->where('cisco', $ciscoName);
                if ($hasTypeExamenColumn) {
                    if ($importType !== '') {
                        $updateQuery->where('type_examen', $importType);
                    } else {
                        $updateQuery->whereNull('type_examen');
                    }
                }
                $updateQuery->update($updateData);

                $updated++;
            } else {
                DB::table('repartition_stats_dren_imports')->insert(array_merge($payload, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
                $created++;
            }
        }

        $response = back()
            ->with('status', "Import recap DREN N-1 terminé: {$created} créé(s), {$updated} mis à jour, {$errors} rejeté(s).")
            ->with('import_rejects', array_slice($rejects, 0, 120));
        AuditLog::record($request, 'import_recap_dren_n1', [
            'created' => $created,
            'updated' => $updated,
            'errors' => $errors,
        ]);
        return $response;
    }

    public function statsReportCentresExcel(Request $request)
    {
        $filters = [
            'annee_n' => (string) $request->query('annee_n', ''),
            'annee_n1' => (string) $request->query('annee_n1', ''),
            'type_examen' => strtoupper((string) $request->query('type_examen', self::TYPE_ALL)),
            'dren' => (string) $request->query('dren', ''),
            'cisco' => (string) $request->query('cisco', ''),
        ];

        if (! in_array($filters['type_examen'], [self::TYPE_ALL, self::TYPE_BEPC, self::TYPE_CEPE], true)) {
            $filters['type_examen'] = self::TYPE_ALL;
        }

        $query = DB::table('repartition_salles as rs')
            ->join('centre_ecrits as ce', 'ce.id', '=', 'rs.centre_ecrit_id')
            ->join('centre_corrections as cc', 'cc.id', '=', 'ce.centre_correction_id')
            ->join('ciscos as cs', 'cs.id', '=', 'cc.cisco_id')
            ->join('drens as d', 'd.id', '=', 'cs.dren_id')
            ->select([
                'rs.annee',
                'rs.langue',
                'rs.numero_salle',
                'rs.effectif',
                'ce.id as centre_ecrit_id',
                'ce.nom as centre_ecrit',
                'cc.nom as centre_correction',
                'cs.nom as cisco',
                'd.nom as dren',
            ]);

        if ($filters['annee_n'] !== '') {
            $query->where('rs.annee', $filters['annee_n']);
        }
        if ($filters['dren'] !== '') {
            $query->where('d.nom', $filters['dren']);
        }
        if ($filters['cisco'] !== '') {
            $query->where('cs.nom', $filters['cisco']);
        }
        if ($filters['type_examen'] === self::TYPE_BEPC) {
            $query->where('rs.langue', '!=', self::CEPE_KEY);
        } elseif ($filters['type_examen'] === self::TYPE_CEPE) {
            $query->where('rs.langue', self::CEPE_KEY);
        }

        $rows = $query->get()->map(function ($row) {
            $row->type_examen = $row->langue === self::CEPE_KEY ? self::TYPE_CEPE : self::TYPE_BEPC;
            return $row;
        });
        $rows = $this->filterOutEmptySalles($rows);

        $currentCentres = $rows
            ->groupBy(fn ($row) => $this->buildCentreKey(
                (string) $row->type_examen,
                (string) $row->dren,
                (string) $row->cisco,
                (string) $row->centre_correction,
                (string) $row->centre_ecrit
            ))
            ->map(function (Collection $group) {
                $first = $group->first();

                return [
                    'type_examen' => (string) $first->type_examen,
                    'dren' => (string) $first->dren,
                    'cisco' => (string) $first->cisco,
                    'centre_correction' => (string) $first->centre_correction,
                    'centre_ecrit' => (string) $first->centre_ecrit,
                    'total_candidats' => (int) $group->sum('effectif'),
                    'total_salles' => $this->countDistinctSalles($group),
                ];
            });

        $previousRowsQuery = DB::table('repartition_stats_imports')
            ->orderBy('dren')
            ->orderBy('cisco')
            ->orderBy('centre_correction')
            ->orderBy('centre_ecrit');

        if ($filters['annee_n1'] !== '') {
            $previousRowsQuery->where('annee', $filters['annee_n1']);
        }
        if ($filters['dren'] !== '') {
            $previousRowsQuery->where('dren', $filters['dren']);
        }
        if ($filters['cisco'] !== '') {
            $previousRowsQuery->where('cisco', $filters['cisco']);
        }
        if ($filters['type_examen'] === self::TYPE_BEPC) {
            $previousRowsQuery->where('type_examen', self::TYPE_BEPC);
        } elseif ($filters['type_examen'] === self::TYPE_CEPE) {
            $previousRowsQuery->where('type_examen', self::TYPE_CEPE);
        }

        $previousRows = $previousRowsQuery->get();
        $previousCentres = collect($previousRows)->keyBy(function ($row) {
            return $this->buildCentreKey(
                (string) $row->type_examen,
                (string) $row->dren,
                (string) $row->cisco,
                (string) $row->centre_correction,
                (string) $row->centre_ecrit
            );
        });

        $comparisonRows = collect(array_unique(array_merge(
            $currentCentres->keys()->all(),
            $previousCentres->keys()->all()
        )))->map(function (string $key) use ($currentCentres, $previousCentres) {
            $current = $currentCentres->get($key);
            $previous = $previousCentres->get($key);
            $currentCandidates = (int) ($current['total_candidats'] ?? 0);
            $previousCandidates = (int) ($previous->total_candidats ?? 0);
            $currentSalles = (int) ($current['total_salles'] ?? 0);
            $previousSalles = (int) ($previous->total_salles ?? 0);

            $status = '';
            if ($current && ! $previous) {
                $status = 'Nouveau centre';
            } elseif (! $current && $previous) {
                $status = "Centre n'existe plus";
            }

            return [
                'dren' => (string) ($current['dren'] ?? $previous?->dren ?? ''),
                'cisco' => (string) ($current['cisco'] ?? $previous?->cisco ?? ''),
                'centre_correction' => (string) ($current['centre_correction'] ?? $previous?->centre_correction ?? ''),
                'centre_ecrit' => (string) ($current['centre_ecrit'] ?? $previous?->centre_ecrit ?? ''),
                'type_examen' => (string) ($current['type_examen'] ?? $previous?->type_examen ?? ''),
                'current_candidats' => $currentCandidates,
                'previous_candidats' => $previousCandidates,
                'current_salles' => $currentSalles,
                'previous_salles' => $previousSalles,
                'ecart_candidats' => $currentCandidates - $previousCandidates,
                'progression_candidats' => $previousCandidates > 0 ? round((($currentCandidates - $previousCandidates) / $previousCandidates) * 100, 1) : ($currentCandidates > 0 ? 100.0 : 0.0),
                'status' => $status,
            ];
        })->sortBy(['dren', 'cisco', 'centre_correction', 'centre_ecrit'])->values();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('COMPARAISON_CENTRES');

        $sheet->fromArray([
            ['Comparaison Centres N / N-1'],
            ['Annee N', $filters['annee_n'] ?: 'Toutes', 'Annee N-1', $filters['annee_n1'] ?: 'Toutes', 'Examen', $filters['type_examen']],
            ['DREN', 'CISCO', 'CENTRE CORRECTION', 'CENTRE ECRIT', 'EXAMEN', 'CANDIDATS N', 'CANDIDATS N-1', 'SALLES N', 'SALLES N-1', 'ECART', 'PROGRESSION %', 'STATUT'],
        ], null, 'A1');

        $sheet->mergeCells('A1:L1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $rowIndex = 4;
        foreach ($comparisonRows as $row) {
            $sheet->fromArray([[
                $row['dren'],
                $row['cisco'],
                $row['centre_correction'],
                $row['centre_ecrit'],
                $row['type_examen'],
                (int) $row['current_candidats'],
                (int) $row['previous_candidats'],
                (int) $row['current_salles'],
                (int) $row['previous_salles'],
                (int) $row['ecart_candidats'],
                (float) $row['progression_candidats'],
                $row['status'],
            ]], null, "A{$rowIndex}");
            $rowIndex++;
        }

        $lastCol = $sheet->getHighestColumn();
        $sheet->getStyle("A3:{$lastCol}3")->getFont()->setBold(true);
        $sheet->getStyle("A3:{$lastCol}3")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');
        $sheet->getStyle("A3:{$lastCol}".($rowIndex - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'comparaison_centres_'.($filters['annee_n'] !== '' ? $filters['annee_n'].'_' : '').($filters['annee_n1'] !== '' ? $filters['annee_n1'].'_' : '').strtolower($filters['type_examen']).'.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function livreControle(Request $request)
    {
        return view('repartition.livre-controle', $this->buildLivreControlePayload($request));
    }

    public function livreControleAuto(Request $request)
    {
        return view('repartition.livre-controle-auto', $this->buildLivreControleAutoPayload($request));
    }

    public function livreControleWord(Request $request)
    {
        $payload = $this->buildLivreControlePayload($request);
        $filename = 'fiche_controle_tracabilite_'.($payload['filters']['annee'] !== '' ? $payload['filters']['annee'].'_' : '').strtolower($payload['filters']['type_examen']).'.doc';
        $content = view('repartition.livre-controle-word', $payload)->render();

        return response($content, 200, [
            'Content-Type' => 'application/msword; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function livrePvAutoWord(Request $request)
    {
        $payload = $this->buildLivreControleAutoPayload($request);
        
        // Parse compteur names and assignments
        $compteurNames = [];
        $drenAssignments = [];
        $groupNames = [];
        
        if ($request->has('compteur_names')) {
            $compteurNames = json_decode($request->input('compteur_names', '{}'), true) ?: [];
        }
        if ($request->has('dren_assignments')) {
            $drenAssignments = json_decode($request->input('dren_assignments', '{}'), true) ?: [];
        }
        if ($request->has('group_names')) {
            $groupNames = json_decode($request->input('group_names', '{}'), true) ?: [];
        }
        
        // Add to payload
        $payload['compteurNames'] = $compteurNames;
        $payload['drenAssignments'] = $drenAssignments;
        $payload['groupNames'] = $groupNames;
        
        $filename = 'pv_tracabilite_'.($payload['filters']['annee'] !== '' ? $payload['filters']['annee'].'_' : '').strtolower($payload['filters']['type_examen']).'.doc';
        $content = view('repartition.livre-pv-auto-word', $payload)->render();

        return response($content, 200, [
            'Content-Type' => 'application/msword; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function livrePdf(Request $request)
    {
        $forcedType = strtoupper((string) $request->query('type_examen', self::TYPE_BEPC));
        if (! in_array($forcedType, [self::TYPE_BEPC, self::TYPE_CEPE], true)) {
            $request->merge(['type_examen' => self::TYPE_BEPC]);
        }

        $bookVariant = (string) $request->query('variant', 'default');
        $isBepcLanguesVariant = $forcedType === self::TYPE_BEPC && $bookVariant === 'langues';

        [$rows, $filters] = $this->getFilteredRows($request);
        $bookData = $this->buildBookData($rows, [
            'bepc_langues_only' => $isBepcLanguesVariant,
        ]);
        $totalGe = collect($bookData)->sum('ge_count');
        $totalPe = collect($bookData)->sum('pe');
        $recapSheets = $this->buildRecapSheets($bookData);
        $centrePagesByDren = $this->paginateCentresByDren($bookData);
        $specialCandidates = DB::table('repartition_salles_specifiques as rss')
            ->join('centre_ecrits as ce', 'ce.id', '=', 'rss.centre_ecrit_id')
            ->join('centre_corrections as cc', 'cc.id', '=', 'ce.centre_correction_id')
            ->join('ciscos as cs', 'cs.id', '=', 'cc.cisco_id')
            ->join('drens as d', 'd.id', '=', 'cs.dren_id')
            ->select([
                'rss.type_examen',
                'rss.annee',
                'd.nom as dren',
                'cs.nom as cisco',
                'cc.nom as centre_correction',
                'ce.nom as centre_ecrit',
                'rss.numero_salle',
                'rss.type_handicap',
                'rss.saisi_par',
            ])
            ->when($filters['annee'] !== '', fn ($query) => $query->where('rss.annee', $filters['annee']))
            ->when($filters['dren'] !== '', fn ($query) => $query->where('d.nom', $filters['dren']))
            ->when($filters['cisco'] !== '', fn ($query) => $query->where('cs.nom', $filters['cisco']))
            ->when($filters['centre_search'] !== '', fn ($query) => $query->where('ce.nom', 'like', '%'.$filters['centre_search'].'%'))
            ->when($filters['type_examen'] === self::TYPE_BEPC, fn ($query) => $query->where('rss.type_examen', self::TYPE_BEPC))
            ->when($filters['type_examen'] === self::TYPE_CEPE, fn ($query) => $query->where('rss.type_examen', self::TYPE_CEPE))
            ->orderBy('rss.annee')
            ->orderBy('d.nom')
            ->orderBy('cs.nom')
            ->orderBy('cc.nom')
            ->orderBy('ce.nom')
            ->orderBy('rss.numero_salle')
            ->get();

        $globalRows = $isBepcLanguesVariant
            ? $rows->filter(fn ($row) => in_array((string) $row->langue, ['Anglais', 'Allemand', 'Esp'], true))
            : $rows;

        $payload = [
            'filters' => $filters,
            'annees' => [],
            'drens' => [],
            'ciscos' => [],
            'centrePagesByDren' => $centrePagesByDren,
            'recapSheets' => $recapSheets,
            'specialCandidates' => $specialCandidates,
            'globalStats' => [
                'total_candidats' => $globalRows->sum('effectif'),
                'total_salles' => $this->countDistinctSalles($globalRows),
                'total_pe' => $totalPe,
                'total_ge' => $totalGe,
                'totaux_langues' => $globalRows
                    ->groupBy(fn ($row) => $row->langue === self::CEPE_KEY ? 'Total CEPE' : $row->langue)
                    ->map(fn (Collection $group) => $group->sum('effectif'))
                    ->sortDesc(),
            ],
            'bookVariant' => $bookVariant,
            'pdfMode' => true,
            'autoPrint' => false,
        ];

        if ($request->boolean('download', false)) {
            $filename = 'livre_repartition_'
                .($filters['annee'] !== '' ? $filters['annee'].'_' : '')
                .strtolower($forcedType)
                .($filters['dren'] !== '' ? '_'.preg_replace('/[^a-z0-9]+/i', '-', $filters['dren']) : '')
                .($filters['cisco'] !== '' ? '_'.preg_replace('/[^a-z0-9]+/i', '-', $filters['cisco']) : '')
                .'.pdf';

            $pdf = Pdf::loadView('repartition.livre-preview-pdf', $payload)
                ->setPaper('a4', 'landscape');

            return $pdf->download($filename);
        }

        $payload['autoPrint'] = false;

        return response()->view('repartition.livre-preview', $payload);
    }

    public function livreExcel(Request $request)
    {
        [$rows, $filters] = $this->getFilteredRows($request);
        $bookData = collect($this->buildBookData($rows));
        $recapSheets = collect($this->buildRecapSheets($bookData->all()));
        $settings = $this->getGlobalSettings();
        $subjectSoubiqueByCentre = $this->buildSubjectSoubiqueRows($rows, $filters['type_examen'], $settings)
            ->keyBy(fn (array $row) => implode('|', [
                $row['dren'],
                $row['cisco'],
                $row['centre_correction'],
                $row['centre_ecrit'],
                $row['type_examen'],
                $row['annee'],
            ]));

        $spreadsheet = new Spreadsheet();
        $recapSheet = $spreadsheet->getActiveSheet();
        $recapSheet->setTitle('RECAP_DREN');

        if ($recapSheets->isEmpty()) {
            $recapSheet->setCellValue('A1', 'Aucune donnee pour les filtres selectionnes.');
        } else {
            $recapHeaders = [
                'DREN',
                'CISCO',
                'CENTRE CORRECTION',
                'CENTRE ECRIT',
                'CANDIDATS',
                'PE',
                'GE TOTAL',
                'REPARTITION GE',
                'SOUBIQUE SUJETS',
            ];
            $recapSheet->fromArray($recapHeaders, null, 'A1');

            $recapRow = 2;
            foreach ($recapSheets as $recap) {
                foreach (($recap['rows'] ?? []) as $line) {
                    $centreKey = implode('|', [
                        $recap['dren'],
                        $line['cisco'],
                        $line['centre_correction'],
                        $line['centre_ecrit'],
                        $line['type_examen'],
                        $line['annee'],
                    ]);
                    $recapSheet->fromArray([
                        $recap['dren'],
                        $line['cisco'],
                        $line['centre_correction'],
                        $line['centre_ecrit'],
                        (int) $line['candidats'],
                        (int) $line['pe'],
                        (int) $line['ge_total'],
                        (string) ($line['ge_repartition'] ?? ''),
                        (int) ($subjectSoubiqueByCentre->get($centreKey)['soubique_sujets'] ?? 0),
                    ], null, "A{$recapRow}");
                    $recapRow++;
                }
            }

            $this->styleSheetWithHeader($recapSheet);
        }

        $this->addCiscoRecapSheet($spreadsheet, $rows);

        $detailSheet = $spreadsheet->createSheet();
        $detailSheet->setTitle('LIVRE_DETAIL');

        if ($bookData->isEmpty()) {
            $detailSheet->setCellValue('A1', 'Aucune donnee detaillee disponible.');
        } else {
            $detailHeaders = [
                'DREN',
                'CISCO',
                'CENTRE CORRECTION',
                'CENTRE ECRIT',
                'TYPE EXAMEN',
                'ANNEE',
                'SALLE',
                'TOTAL CEPE',
                'ANGLAIS',
                'ESP',
                'ALLEMAND',
                'OPTION B',
                'ETRANGER OPTION A',
                'ETRANGER OPTION B',
                'TOTAL SALLE',
                'PE',
                'GE TOTAL',
                'REPARTITION GE',
                'SOUBIQUE SUJETS',
                'AXE DISPATCHING',
                'POINT LARGAGE',
            ];
            $detailSheet->fromArray($detailHeaders, null, 'A1');

            $detailRows = $rows
                ->groupBy(fn ($row) => implode('|', [
                    $row->dren,
                    $row->cisco,
                    $row->centre_correction,
                    $row->centre_ecrit,
                    $row->type_examen,
                    $row->annee,
                    $row->centre_ecrit_id,
                    $row->numero_salle,
                ]))
                ->map(function (Collection $salleRows) use ($bookData, $subjectSoubiqueByCentre) {
                    $first = $salleRows->first();
                    $centre = $bookData->first(function (array $item) use ($first) {
                        return $item['dren'] === $first->dren
                            && $item['cisco'] === $first->cisco
                            && $item['centre_correction'] === $first->centre_correction
                            && $item['centre_ecrit'] === $first->centre_ecrit
                            && $item['type_examen'] === $first->type_examen
                            && $item['annee'] === $first->annee;
                    });

                    return [
                        'dren' => (string) $first->dren,
                        'cisco' => (string) $first->cisco,
                        'centre_correction' => (string) $first->centre_correction,
                        'centre_ecrit' => (string) $first->centre_ecrit,
                        'type_examen' => (string) $first->type_examen,
                        'annee' => (string) $first->annee,
                        'salle' => (int) $first->numero_salle,
                        'total_cepe' => (int) $salleRows->where('langue', self::CEPE_KEY)->sum('effectif'),
                        'anglais' => (int) $salleRows->where('langue', 'Anglais')->sum('effectif'),
                        'esp' => (int) $salleRows->where('langue', 'Esp')->sum('effectif'),
                        'allemand' => (int) $salleRows->where('langue', 'Allemand')->sum('effectif'),
                        'option_b' => (int) $salleRows->where('langue', 'Option B')->sum('effectif'),
                        'etranger_option_a' => $this->sumForeignOption($salleRows, 'A'),
                        'etranger_option_b' => $this->sumForeignOption($salleRows, 'B'),
                        'total_salle' => (int) $salleRows->sum('effectif'),
                        'pe' => (int) ($centre['pe'] ?? 0),
                        'ge_count' => (int) ($centre['ge_count'] ?? 0),
                        'ge_distribution' => implode('+', array_map(fn (int $n) => (string) $n, $centre['ge_distribution'] ?? [])),
                        'soubique_sujets' => (int) ($subjectSoubiqueByCentre->get(implode('|', [
                            (string) $first->dren,
                            (string) $first->cisco,
                            (string) $first->centre_correction,
                            (string) $first->centre_ecrit,
                            (string) $first->type_examen,
                            (string) $first->annee,
                        ]))['soubique_sujets'] ?? 0),
                        'axe_dispatching' => (string) ($first->axe_dispatching ?? ''),
                        'point_largage' => (string) ($first->point_largage ?? ''),
                    ];
                })
                ->sortBy(['dren', 'cisco', 'centre_correction', 'centre_ecrit', 'annee', 'salle'])
                ->values();

            $detailRow = 2;
            $centreSeen = [];
            foreach ($detailRows as $line) {
                $centreKey = implode('|', [
                    $line['dren'],
                    $line['cisco'],
                    $line['centre_correction'],
                    $line['centre_ecrit'],
                    $line['type_examen'],
                    $line['annee'],
                ]);
                $firstSalleForCentre = ! isset($centreSeen[$centreKey]);
                $centreSeen[$centreKey] = true;

                $detailSheet->fromArray([
                    $line['dren'],
                    $line['cisco'],
                    $line['centre_correction'],
                    $line['centre_ecrit'],
                    $line['type_examen'],
                    $line['annee'],
                    $line['salle'],
                    $line['total_cepe'],
                    $line['anglais'],
                    $line['esp'],
                    $line['allemand'],
                    $line['option_b'],
                    $line['etranger_option_a'],
                    $line['etranger_option_b'],
                    $line['total_salle'],
                    $firstSalleForCentre ? $line['pe'] : 0,
                    $firstSalleForCentre ? $line['ge_count'] : 0,
                    $firstSalleForCentre ? $line['ge_distribution'] : '',
                    $firstSalleForCentre ? $line['soubique_sujets'] : 0,
                    $line['axe_dispatching'],
                    $line['point_largage'],
                ], null, "A{$detailRow}");
                $detailRow++;
                }

            $this->styleSheetWithHeader($detailSheet);
        }

        $pdfLikeSheet = $spreadsheet->createSheet();
        $pdfLikeSheet->setTitle('LIVRE_FORMAT_PDF');
        $pdfLikeRow = 1;
        if ($bookData->isEmpty()) {
            $pdfLikeSheet->setCellValue('A1', 'Aucune donnee pour les filtres selectionnes.');
        } else {
            $isBepc = ($filters['type_examen'] ?? self::TYPE_ALL) === self::TYPE_BEPC;

            if ($isBepc && $recapSheets->isNotEmpty()) {
                foreach ($recapSheets as $recap) {
                    $pdfLikeSheet->setCellValue("A{$pdfLikeRow}", 'Feuille recap DREN: '.$recap['dren']);
                    $pdfLikeSheet->mergeCells("A{$pdfLikeRow}:G{$pdfLikeRow}");
                    $pdfLikeSheet->getStyle("A{$pdfLikeRow}")->getFont()->setBold(true);
                    $pdfLikeRow++;

                    $pdfLikeSheet->setCellValue("A{$pdfLikeRow}", 'C correction: '.$recap['total_correction']);
                    $pdfLikeSheet->setCellValue("E{$pdfLikeRow}", 'Candidats: '.$recap['total_candidats']);
                    $pdfLikeRow++;
                    $pdfLikeSheet->setCellValue("A{$pdfLikeRow}", "Centre d'ecrit: ".$recap['total_ecrit']);
                    $pdfLikeSheet->setCellValue("E{$pdfLikeRow}", 'Salle: '.$recap['total_salles']);
                    $pdfLikeRow++;
                    $pdfLikeSheet->setCellValue("A{$pdfLikeRow}", 'Total GE/Matiere: '.$recap['total_ge']);
                    $pdfLikeSheet->setCellValue("E{$pdfLikeRow}", 'GE: '.$recap['total_ge']);
                    $pdfLikeRow += 2;

                    $pdfLikeSheet->fromArray([[
                        'CISCO',
                        'centre de correction',
                        'CENTRE',
                        'CANDIDATS',
                        'Salles',
                        'GE/Matieres',
                        'Répartition GE',
                    ]], null, "A{$pdfLikeRow}");
                    $pdfLikeSheet->getStyle("A{$pdfLikeRow}:G{$pdfLikeRow}")->getFont()->setBold(true);
                    $pdfLikeRow++;

                    foreach (($recap['rows'] ?? []) as $line) {
                        $pdfLikeSheet->fromArray([[
                            $line['cisco'],
                            $line['centre_correction'],
                            $line['centre_ecrit'],
                            (int) $line['candidats'],
                            (int) $line['salles'],
                            (int) $line['ge_total'],
                            (string) ($line['ge_repartition'] ?? ''),
                        ]], null, "A{$pdfLikeRow}");
                        $pdfLikeRow++;
                    }

                    $pdfLikeRow += 2;
                }
            }

            foreach ($bookData as $centre) {
                if (($filters['type_examen'] ?? self::TYPE_ALL) === self::TYPE_BEPC && ($centre['type_examen'] ?? '') !== self::TYPE_BEPC) {
                    continue;
                }

                if ($isBepc) {
                    $langueOrder = ['Anglais', 'Allemand', 'Esp', 'Option B'];
                    $langueLabels = [
                        'Anglais' => 'Ang',
                        'Allemand' => 'ALL',
                        'Esp' => 'ESP',
                        'Option B' => 'B',
                    ];
                    $langueLines = [];
                    foreach ($langueOrder as $langue) {
                        $total = (int) ($centre['langue_totals'][$langue] ?? 0);
                        if ($total <= 0) {
                            continue;
                        }
                        $salleCount = (int) ($centre['langue_salles'][$langue] ?? 0);
                        $label = $langueLabels[$langue] ?? $langue;
                        $langueLines[] = "Total cddts {$label}: {$total} total salle {$label}: {$salleCount}";
                    }

                    if ($langueLines === []) {
                        $langueLines[] = 'Total par langues: 0';
                    }

                    $maxSalles = collect($centre['tables'] ?? [])
                        ->map(fn ($table) => count($table['salles'] ?? []))
                        ->max() ?? 0;
                    $rightColIndex = max(7, 2 + (int) $maxSalles);
                    $rightCol = Coordinate::stringFromColumnIndex($rightColIndex);

                    $summaryStart = $pdfLikeRow;
                    foreach ($langueLines as $line) {
                        $pdfLikeSheet->setCellValue("A{$pdfLikeRow}", $line);
                        $pdfLikeRow++;
                    }

                    $pdfLikeSheet->setCellValue("{$rightCol}{$summaryStart}", 'Total candidats: '.$centre['total_candidats']);
                    $pdfLikeSheet->setCellValue("{$rightCol}".($summaryStart + 1), 'Total salle: '.$centre['total_salles']);
                    $pdfLikeSheet->setCellValue("{$rightCol}".($summaryStart + 2), 'Total GE/Matiere: '.$centre['ge_count']);
                    $pdfLikeRow = max($pdfLikeRow, $summaryStart + 3);
                    $pdfLikeRow++;
                } else {
                    $pdfLikeSheet->setCellValue("A{$pdfLikeRow}", 'CENTRE: '.$centre['centre_ecrit'].' ('.$centre['type_examen'].')');
                    $pdfLikeSheet->mergeCells("A{$pdfLikeRow}:H{$pdfLikeRow}");
                    $pdfLikeSheet->getStyle("A{$pdfLikeRow}")->getFont()->setBold(true);
                    $pdfLikeRow++;

                    $pdfLikeSheet->fromArray([
                        ['DREN', $centre['dren'], 'ANNEE', $centre['annee'], 'CISCO', $centre['cisco'], 'CENTRE CORRECTION', $centre['centre_correction']],
                        ['Axe dispatching', (string) ($centre['axe_dispatching'] ?? '-'), 'Point largage', (string) ($centre['point_largage'] ?? '-'), 'PE', (int) $centre['pe'], 'GE', (int) $centre['ge_count']],
                        ['Repartition GE', implode('+', array_map(fn (int $n) => (string) $n, $centre['ge_distribution'] ?? [])), '', '', '', '', '', ''],
                    ], null, "A{$pdfLikeRow}");
                    $pdfLikeSheet->mergeCells("B{$pdfLikeRow}:H{$pdfLikeRow}");
                    $pdfLikeSheet->mergeCells("B".($pdfLikeRow + 1).":D".($pdfLikeRow + 1));
                    $pdfLikeSheet->mergeCells("F".($pdfLikeRow + 1).":G".($pdfLikeRow + 1));
                    $pdfLikeSheet->mergeCells("B".($pdfLikeRow + 2).":H".($pdfLikeRow + 2));
                    $pdfLikeRow += 4;
                }

                foreach (($centre['tables'] ?? []) as $table) {
                    if ($isBepc) {
                        $rowspan = count($table['rows'] ?? []) + 2;
                        $startRow = $pdfLikeRow;
                        $pdfLikeSheet->setCellValue("A{$startRow}", $centre['centre_ecrit']);
                        $pdfLikeSheet->mergeCells("A{$startRow}:A".($startRow + $rowspan - 1));
                        $pdfLikeSheet->setCellValue("B{$startRow}", 'Salles');

                        $colIndex = 3;
                        foreach (($table['salles'] ?? []) as $salle) {
                            $col = Coordinate::stringFromColumnIndex($colIndex);
                            $pdfLikeSheet->setCellValue("{$col}{$startRow}", 'S'.(int) $salle);
                            $colIndex++;
                        }
                        $headerLastCol = Coordinate::stringFromColumnIndex(max($colIndex - 1, 3));
                        $pdfLikeSheet->getStyle("A{$startRow}:{$headerLastCol}{$startRow}")->getFont()->setBold(true);
                        $pdfLikeRow++;

                        foreach (($table['rows'] ?? []) as $langueRow) {
                            $pdfLikeSheet->setCellValue("B{$pdfLikeRow}", $langueRow['label']);
                            $colIndex = 3;
                            foreach (($table['salles'] ?? []) as $salle) {
                                $col = Coordinate::stringFromColumnIndex($colIndex);
                                $pdfLikeSheet->setCellValue("{$col}{$pdfLikeRow}", (int) ($langueRow['values'][$salle] ?? 0));
                                $colIndex++;
                            }
                            $pdfLikeRow++;
                        }

                        $pdfLikeSheet->setCellValue("B{$pdfLikeRow}", 'Total');
                        $colIndex = 3;
                        foreach (($table['salles'] ?? []) as $salle) {
                            $col = Coordinate::stringFromColumnIndex($colIndex);
                            $pdfLikeSheet->setCellValue("{$col}{$pdfLikeRow}", (int) (($table['totaux_salles'][$salle] ?? 0)));
                            $colIndex++;
                        }
                        $pdfLikeSheet->getStyle("A{$startRow}:{$headerLastCol}{$pdfLikeRow}")
                            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                        $pdfLikeRow += 2;
                    } else {
                        $headers = ['Langue / Option'];
                        foreach (($table['salles'] ?? []) as $salle) {
                            $headers[] = 'Salle '.(int) $salle;
                        }
                        $headers[] = 'Total langue';
                        $pdfLikeSheet->fromArray([$headers], null, "A{$pdfLikeRow}");
                        $headerLastCol = Coordinate::stringFromColumnIndex(count($headers));
                        $pdfLikeSheet->getStyle("A{$pdfLikeRow}:{$headerLastCol}{$pdfLikeRow}")->getFont()->setBold(true);
                        $pdfLikeSheet->getStyle("A{$pdfLikeRow}:{$headerLastCol}{$pdfLikeRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');
                        $pdfLikeRow++;

                        foreach (($table['rows'] ?? []) as $langueRow) {
                            $line = [$langueRow['label']];
                            $lineTotal = 0;
                            foreach (($table['salles'] ?? []) as $salle) {
                                $value = (int) ($langueRow['values'][$salle] ?? 0);
                                $line[] = $value;
                                $lineTotal += $value;
                            }
                            $line[] = $lineTotal;
                            $pdfLikeSheet->fromArray([$line], null, "A{$pdfLikeRow}");
                            $pdfLikeRow++;
                        }

                        $totalRow = ['TOTAL SALLE'];
                        $grandTotal = 0;
                        foreach (($table['salles'] ?? []) as $salle) {
                            $salleTotal = (int) (($table['totaux_salles'][$salle] ?? 0));
                            $totalRow[] = $salleTotal;
                            $grandTotal += $salleTotal;
                        }
                        $totalRow[] = $grandTotal;
                        $pdfLikeSheet->fromArray([$totalRow], null, "A{$pdfLikeRow}");
                        $pdfLikeSheet->getStyle("A{$pdfLikeRow}:{$headerLastCol}{$pdfLikeRow}")->getFont()->setBold(true);
                        $pdfLikeSheet->getStyle("A{$pdfLikeRow}:{$headerLastCol}{$pdfLikeRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF8FAFC');
                        $pdfLikeSheet->getStyle("A".($pdfLikeRow - (count($table['rows'] ?? []) + 1)).":{$headerLastCol}{$pdfLikeRow}")
                            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                        $pdfLikeRow += 2;
                    }
                }

                $pdfLikeRow++;
            }

            $pdfLikeLastCol = $pdfLikeSheet->getHighestColumn();
            $pdfLikeLastColIndex = Coordinate::columnIndexFromString($pdfLikeLastCol);
            for ($i = 1; $i <= $pdfLikeLastColIndex; $i++) {
                $pdfLikeSheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
            }
        }

        $specSheet = $spreadsheet->createSheet();
        $specSheet->setTitle('BESOINS_SPECIAUX');
        $specQuery = DB::table('repartition_salles_specifiques as rss')
            ->join('centre_ecrits as ce', 'ce.id', '=', 'rss.centre_ecrit_id')
            ->join('centre_corrections as cc', 'cc.id', '=', 'ce.centre_correction_id')
            ->join('ciscos as cs', 'cs.id', '=', 'cc.cisco_id')
            ->join('drens as d', 'd.id', '=', 'cs.dren_id')
            ->select([
                'rss.type_examen',
                'rss.annee',
                'd.nom as dren',
                'cs.nom as cisco',
                'cc.nom as centre_correction',
                'ce.nom as centre_ecrit',
                'rss.numero_salle',
                'rss.type_handicap',
                'rss.saisi_par',
            ])
            ->orderBy('rss.annee')
            ->orderBy('d.nom')
            ->orderBy('cs.nom')
            ->orderBy('cc.nom')
            ->orderBy('ce.nom')
            ->orderBy('rss.numero_salle');

        if (($filters['annee'] ?? '') !== '') {
            $specQuery->where('rss.annee', $filters['annee']);
        }
        if (($filters['dren'] ?? '') !== '') {
            $specQuery->where('d.nom', $filters['dren']);
        }

        $specRows = $specQuery->get();
        if ($specRows->isEmpty()) {
            $specSheet->setCellValue('A1', 'Aucune donnée de besoins spécifiques.');
        } else {
            $specSheet->fromArray([
                'TYPE EXAMEN',
                'ANNEE',
                'DREN',
                'CISCO',
                'CENTRE CORRECTION',
                'CENTRE ECRIT',
                'SALLE',
                'TYPE HANDICAP',
                'SAISI PAR',
            ], null, 'A1');

            $rowIndex = 2;
            foreach ($specRows as $row) {
                $specSheet->fromArray([
                    $row->type_examen,
                    $row->annee,
                    $row->dren,
                    $row->cisco,
                    $row->centre_correction,
                    $row->centre_ecrit,
                    (int) $row->numero_salle,
                    $row->type_handicap,
                    $row->saisi_par,
                ], null, "A{$rowIndex}");
                $rowIndex++;
            }

            $this->styleSheetWithHeader($specSheet);
        }

        $spreadsheet->setActiveSheetIndex(0);
        $writer = new Xlsx($spreadsheet);
        $fileName = 'livre_repartition_'.($filters['annee'] !== '' ? $filters['annee'].'_' : '').strtolower($filters['type_examen']).'.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function cepeLivraison(Request $request)
    {
        $payload = $this->buildCepeLivraisonPayload($request);

        return view('repartition.livraison-cepe', [
            'filters' => $payload['filters'],
            'annees' => $payload['annees'],
            'drens' => $payload['drens'],
            'pagesBySubject' => $payload['pagesBySubject'],
            'otherSubjectsCount' => $payload['otherSubjectsCount'],
            'params' => $payload['params'],
            'pagesTotalParCandidat' => $payload['pagesTotalParCandidat'],
            'rows' => $payload['rows'],
            'global' => $payload['global'],
        ]);
    }

    public function cepeLivraisonExcel(Request $request)
    {
        $payload = $this->buildCepeLivraisonPayload($request);
        $rows = collect($payload['rows']);
        $global = $payload['global'];
        $pagesBySubject = $payload['pagesBySubject'];
        $params = $payload['params'];
        $filters = $payload['filters'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('LIVRAISON_CEPE');

        $sheet->fromArray([
            ['LIVRAISON CEPE PAR CISCO'],
            ['Annee', $filters['annee'] !== '' ? $filters['annee'] : 'Toutes', 'DREN', $filters['dren'] !== '' ? $filters['dren'] : 'Toutes'],
            ['GE/soubique', $params['ge_par_soubique'], 'Enveloppes/barre cire', $params['enveloppes_par_barre_cire'], 'Pages/RAM', $params['pages_par_ram']],
            ['Marqueur fixe/CISCO', $params['marqueur_fixe_par_cisco'], 'Marqueur/soubique', $params['marqueur_par_soubique']],
            ['Marge PE (%)', $params['pe_margin_percent'], 'Marge GE (%)', $params['ge_margin_percent']],
            ['Francais', $pagesBySubject['francais'], 'Connaissances usuelles (CU)', $pagesBySubject['connaissances_usuelles'], 'Geographie', $pagesBySubject['geographie']],
            ['Malagasy', $pagesBySubject['malagasy'], 'Operation', $pagesBySubject['operation'], 'Probleme', $pagesBySubject['probleme']],
            ['TFFMOM', $pagesBySubject['tffmom'], 'Total pages/candidat', $payload['pagesTotalParCandidat']],
            ['DREN', 'CISCO', 'Candidats', 'Salles', 'PE', 'GE total', 'GE Probleme (3PE)', 'GE Autres matieres (6PE)', 'Soubique', 'Ficelle', 'Cire', 'Pages total', 'Papier RAM', 'Marqueur'],
        ], null, 'A1');

        $sheet->mergeCells('A1:N1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $rowIndex = 10;
        foreach ($rows as $row) {
            $sheet->fromArray([[
                $row['dren'],
                $row['cisco'],
                (int) $row['candidats'],
                (int) $row['salles'],
                (int) $row['pe'],
                (int) $row['ge'],
                (int) $row['ge_probleme'],
                (int) $row['ge_autres'],
                (int) $row['soubique'],
                (int) $row['ficelle'],
                (int) $row['cire'],
                (int) $row['pages_total'],
                (int) $row['papier_ram'],
                (int) $row['marqueur'],
            ]], null, "A{$rowIndex}");
            $rowIndex++;
        }

        $sheet->fromArray([[
            'TOTAL',
            '',
            (int) $global['total_candidats'],
            (int) $global['total_salles'],
            (int) $global['total_pe'],
            (int) $global['total_ge'],
            (int) $global['total_ge_probleme'],
            (int) $global['total_ge_autres'],
            (int) $global['total_soubique'],
            (int) $global['total_ficelle'],
            (int) $global['total_cire'],
            '',
            (int) $global['total_ram'],
            (int) $global['total_marqueur'],
        ]], null, "A{$rowIndex}");

        $sheet->getStyle("A9:N9")->getFont()->setBold(true);
        $sheet->getStyle("A9:N9")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');
        $sheet->getStyle("A9:N{$rowIndex}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$rowIndex}:N{$rowIndex}")->getFont()->setBold(true);
        for ($i = 1; $i <= 14; $i++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'livraison_cepe_'.($filters['annee'] !== '' ? $filters['annee'].'_' : '').'cisco.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function bepcCopies(Request $request)
    {
        $payload = $this->buildBepcCopiesPayload($request);

        return view('repartition.bepc-copies', $payload + [
            'pdfMode' => false,
        ]);
    }

    public function bepcCopiesPdf(Request $request)
    {
        $payload = $this->buildBepcCopiesPayload($request);

        $fileName = 'repartition_bepc_copies_'.($payload['filters']['annee'] ?? 'export').'.pdf';

        $pdf = Pdf::loadView('repartition.bepc-copies', $payload + [
            'pdfMode' => true,
        ])
            ->setPaper('a4', 'portrait')
            ->setOption(['isHtml5ParserEnabled' => true]);

        return $pdf->download($fileName);
    }

    public function saveBepcCopyPostalCode(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'annee' => ['required', 'string', 'max:9'],
            'cisco_id' => ['required', 'integer', 'exists:ciscos,id'],
            'code_postal' => ['required', 'string', 'max:50'],
        ]);

        BepcCopyDistribution::updateOrCreate(
            [
                'annee' => trim((string) $validated['annee']),
                'cisco_id' => (int) $validated['cisco_id'],
            ],
            [
                'code_postal' => strtoupper(trim((string) $validated['code_postal'])),
            ]
        );

        return back()->with('status', 'Code postal enregistré.');
    }

    public function bepcCopiesExcel(Request $request)
    {
        $payload = $this->buildBepcCopiesPayload($request);
        $rows = collect($payload['rows']);
        $filters = $payload['filters'];
        $global = $payload['globalStats'];
        $marginPercent = $payload['marginPercent'];
        $roundingModeLabel = $filters['rounding_mode'] === 'down' ? 'Arrondi moins' : 'Arrondi plus';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('FEUILLES_BEPC');

        $sheet->fromArray([
            ['REPARTITION FEUILLES BEPC'],
            ['REPARTITION DES FEUILLES DE COPIES BEPC PAR CISCO'],
            ['Annee', $filters['annee'] !== '' ? $filters['annee'] : 'Toutes', 'DREN', $filters['dren'] !== '' ? $filters['dren'] : 'Toutes'],
            ['Marge generale (%)', $marginPercent],
            ['Mode arrondi', $roundingModeLabel],
            ['Surplus langue absent', $filters['add_missing_langue_surplus'] ? 'Oui' : 'Non', 'Pas surplus', $filters['missing_langue_surplus_step']],
            ['Fusion petit lot', $filters['merge_small_soubique'] ? 'Oui' : 'Non', 'Capacite fusion', $filters['merge_small_soubique_capacity']],
            ['DREN', 'CISCO', 'TYPE', 'CENTRES SANS LANGUE', '% SANS LANGUE', 'CODE POSTAL', 'TOTAL CANDIDATS', 'FEUILLES DOUBLE', 'FEUILLES DOUBLE ARRONDIES', 'FEUILLES SIMPLE', 'FEUILLES SIMPLE ARRONDIES', 'SURPLUS', 'SOUBIQUE'],
        ], null, 'A1');

        $sheet->mergeCells('A1:M1');
        $sheet->mergeCells('A2:M2');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A1:M2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $rowIndex = 9;
        foreach ($rows as $row) {
            $soubiqueLabel = ((int) ($row['soubique_mixte'] ?? 0)) > 0
                ? ((int) $row['soubique_total']).' (Mixte: '.((int) $row['soubique_mixte']).')'
                : ((int) $row['soubique_total']).' (Double: '.((int) $row['soubique_feuilles_double']).' | Simple: '.((int) $row['soubique_feuilles_simple']).')';

            $sheet->fromArray([[
                $row['dren'],
                $row['cisco'],
                $row['row_type'] === 'centre_isole' ? 'CENTRE ISOLE' : 'CISCO',
                (int) $row['centres_sans_langue'].' / '.(int) $row['total_centres'],
                (float) $row['centres_sans_langue_percent'],
                $row['code_postal'],
                (int) $row['total_candidats'],
                (int) $row['feuilles_double'],
                (int) $row['feuilles_double_arrondies'],
                (int) $row['feuilles_simple'],
                (int) $row['feuilles_simple_arrondies'],
                (int) $row['missing_langue_surplus_sheets'],
                $soubiqueLabel,
            ]], null, "A{$rowIndex}");
            $rowIndex++;
        }

        $sheet->fromArray([[
            'TOTAL',
            '',
            '',
            '',
            '',
            '',
            (int) $global['total_candidats'],
            (int) $global['total_feuilles_double'],
            (int) $global['total_feuilles_double_arrondies'],
            (int) $global['total_feuilles_simple'],
            (int) $global['total_feuilles_simple_arrondies'],
            (int) $global['total_missing_langue_surplus_sheets'],
            ((int) $global['total_soubiques']).(((int) ($global['total_soubiques_mixte'] ?? 0)) > 0 ? ' (Mixte: '.((int) $global['total_soubiques_mixte']).')' : ''),
        ]], null, "A{$rowIndex}");

        $sheet->getStyle('A8:M8')->getFont()->setBold(true);
        $sheet->getStyle('A8:M8')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');
        $sheet->getStyle("A8:M{$rowIndex}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$rowIndex}:M{$rowIndex}")->getFont()->setBold(true);

        foreach (range(1, 13) as $i) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'feuilles_copies_bepc_'.($filters['annee'] !== '' ? $filters['annee'].'_' : '').'cisco.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function bepcCopiesWord(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $payload = $this->buildBepcCopiesPayload($request);
        $content = view('repartition.bepc-copies-word', $payload)->render();
        $fileName = 'accuse_reception_centre_bepc_'.($payload['filters']['annee'] !== '' ? $payload['filters']['annee'].'_' : '').'word.doc';

        return response($content, 200, [
            'Content-Type' => 'application/msword; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    public function subjectSoubiqueSimulation(Request $request)
    {
        [$rows, $filters, $annees, $drens, $ciscos] = $this->getFilteredRows($request);
        $settings = $this->getGlobalSettings();
        $simulationRows = $this->buildSubjectSoubiqueRows($rows, $filters['type_examen'], $settings);

        return view('repartition.subject-soubique-simulation', [
            'filters' => $filters,
            'annees' => $annees,
            'drens' => $drens,
            'ciscos' => $ciscos,
            'rows' => $simulationRows,
            'settings' => $settings,
            'globalStats' => [
                'total_centres' => $simulationRows->count(),
                'total_candidats' => (int) $simulationRows->sum('total_candidats'),
                'total_salles' => (int) $simulationRows->sum('total_salles'),
                'total_soubiques_sujets' => (int) $simulationRows->sum('soubique_sujets'),
            ],
        ]);
    }

    public function subjectSoubiquePv(Request $request)
    {
        [$rows, $filters, $annees, $drens, $ciscos] = $this->getFilteredRows($request);
        $settings = $this->getGlobalSettings();
        $simulationRows = $this->buildSubjectSoubiqueRows($rows, $filters['type_examen'], $settings);

        return view('repartition.subject-soubique-pv', [
            'filters' => $filters,
            'annees' => $annees,
            'drens' => $drens,
            'ciscos' => $ciscos,
            'rows' => $simulationRows,
            'settings' => $settings,
        ]);
    }

    public function subjectPrintSimulation(Request $request)
    {
        return view('repartition.subject-print-simulation', $this->buildSubjectPrintPayload($request));
    }

    public function subjectPrintExcel(Request $request)
    {
        $payload = $this->buildSubjectPrintPayload($request);
        $filters = $payload['filters'];
        $subjectTotals = collect($payload['subjectTotals']);
        $printMode = $payload['printMode'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('TIRAGE');

        if ($printMode === 'sord') {
            $sheet->fromArray([
                ['EXPORT TIRAGE SORD'],
                ['Annee', $filters['annee'] !== '' ? $filters['annee'] : 'Toutes', 'Type', $filters['type_examen'] !== '' ? $filters['type_examen'] : 'Tous'],
                ['DREN', $filters['dren'] !== '' ? $filters['dren'] : 'Toutes', 'CISCO', $filters['cisco'] !== '' ? $filters['cisco'] : 'Tous'],
                ['Matiere', $filters['subject'] !== '' ? ($payload['subjectOptions'][$filters['subject']] ?? $filters['subject']) : 'Toutes', 'Marge SORD/salle', (int) ($filters['sord_margin_per_room'] ?? 5)],
                ['PAGE', 'PAGE MERE', 'MATIERES', 'CANDIDAT', 'MARGE', 'CANDIDAT + MARGE', 'TOTAL A TIRER MERE', 'TOTAL A TIRER SIMPLE', 'TOTAL TIRAGE', 'RAMES', 'ORDRE DE TIRAGE'],
            ], null, 'A1');

            $rowIndex = 6;
            $exportRows = $subjectTotals
                ->map(function (array $subject) {
                    $segments = collect($subject['segments'] ?? [])->sortByDesc('pages')->values();
                    $mainSegment = $segments->first();
                    $simpleSegment = $segments->slice(1)->first();
                    $totalTirage = (int) ($subject['total_feuilles'] ?? 0);

                    return [
                        'page' => (int) ($subject['pages'] ?? 0),
                        'page_mere' => (int) ($mainSegment['copies_per_sheet'] ?? 0),
                        'matiere' => (string) ($subject['label'] ?? ''),
                        'candidat' => (int) ($subject['total_candidates'] ?? 0),
                        'marge' => (int) ($subject['total_margin_surplus'] ?? 0),
                        'candidat_avec_marge' => (int) (($subject['total_candidates'] ?? 0) + ($subject['total_margin_surplus'] ?? 0)),
                        'total_a_tirer_mere' => (int) ($mainSegment['feuilles'] ?? 0),
                        'total_a_tirer_simple' => (int) ($simpleSegment['feuilles'] ?? 0),
                        'total_tirage' => $totalTirage,
                        'rames' => (int) ceil($totalTirage / 500),
                        'ordre_tirage' => (int) ($subject['order_index'] ?? 999),
                    ];
                })
                ->sortBy('ordre_tirage')
                ->values();

            foreach ($exportRows as $row) {
                $sheet->fromArray([[
                    $row['page'],
                    $row['page_mere'],
                    $row['matiere'],
                    $row['candidat'],
                    $row['marge'],
                    $row['candidat_avec_marge'],
                    $row['total_a_tirer_mere'],
                    $row['total_a_tirer_simple'],
                    $row['total_tirage'],
                    $row['rames'],
                    $row['ordre_tirage'],
                ]], null, "A{$rowIndex}");
                $rowIndex++;
            }

            $sheet->fromArray([[
                'TOTAL',
                '',
                '',
                (int) $exportRows->sum('candidat'),
                (int) $exportRows->sum('marge'),
                (int) $exportRows->sum('candidat_avec_marge'),
                (int) $exportRows->sum('total_a_tirer_mere'),
                (int) $exportRows->sum('total_a_tirer_simple'),
                (int) $exportRows->sum('total_tirage'),
                (int) $exportRows->sum('rames'),
                '',
            ]], null, "A{$rowIndex}");

            $sheet->mergeCells('A1:K1');
            $sheet->getStyle('A1:K1')->getFont()->setBold(true);
            $sheet->getStyle('A5:K5')->getFont()->setBold(true);
            $sheet->getStyle('A5:K5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');
            $sheet->getStyle("A5:K{$rowIndex}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle("A{$rowIndex}:K{$rowIndex}")->getFont()->setBold(true);
            foreach (range('A', 'K') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $drenSheet = $spreadsheet->createSheet();
            $drenSheet->setTitle('TIRAGE_DREN');
            $drenSheet->fromArray([
                ['SYNTHESE TIRAGE PAR DREN'],
                ['DREN', 'CENTRES', 'SALLES', 'MARGE', 'EXEMPLAIRES', 'FEUILLES', 'IMPRESSIONS'],
            ], null, 'A1');

            $drenRowIndex = 3;
            foreach (collect($payload['drenRows']) as $row) {
                $drenSheet->fromArray([[
                    $row['dren'],
                    (int) $row['total_centres'],
                    (int) $row['total_salles'],
                    (int) $row['total_margin_surplus'],
                    (int) $row['total_exemplaires'],
                    (int) $row['total_feuilles'],
                    (int) $row['total_impressions'],
                ]], null, "A{$drenRowIndex}");
                $drenRowIndex++;
            }

            $drenSheet->fromArray([[
                'TOTAL',
                (int) collect($payload['drenRows'])->sum('total_centres'),
                (int) collect($payload['drenRows'])->sum('total_salles'),
                (int) collect($payload['drenRows'])->sum('total_margin_surplus'),
                (int) collect($payload['drenRows'])->sum('total_exemplaires'),
                (int) collect($payload['drenRows'])->sum('total_feuilles'),
                (int) collect($payload['drenRows'])->sum('total_impressions'),
            ]], null, "A{$drenRowIndex}");

            $drenSheet->mergeCells('A1:G1');
            $drenSheet->getStyle('A1:G1')->getFont()->setBold(true);
            $drenSheet->getStyle('A2:G2')->getFont()->setBold(true);
            $drenSheet->getStyle('A2:G2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');
            $drenSheet->getStyle("A2:G{$drenRowIndex}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $drenSheet->getStyle("A{$drenRowIndex}:G{$drenRowIndex}")->getFont()->setBold(true);
            foreach (range('A', 'G') as $col) {
                $drenSheet->getColumnDimension($col)->setAutoSize(true);
            }
        } else {
            $sheet->fromArray([
                ['EXPORT TIRAGE A4'],
                ['MATIERE', 'PAGES', 'SALLES', 'CANDIDATS', 'MARGE', 'EXEMPLAIRES', 'FEUILLES', 'IMPRESSIONS'],
            ], null, 'A1');

            $rowIndex = 3;
            foreach ($subjectTotals as $subject) {
                $sheet->fromArray([[
                    $subject['label'],
                    (int) $subject['pages'],
                    (int) $subject['total_room_count'],
                    (int) $subject['total_candidates'],
                    (int) $subject['total_margin_surplus'],
                    (int) $subject['total_exemplaires'],
                    (int) $subject['total_feuilles'],
                    (int) $subject['total_impressions'],
                ]], null, "A{$rowIndex}");
                $rowIndex++;
            }

            $sheet->getStyle('A2:H2')->getFont()->setBold(true);
            $sheet->getStyle('A2:H2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');
            $sheet->getStyle("A2:H{$rowIndex}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            foreach (range('A', 'H') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $drenSheet = $spreadsheet->createSheet();
            $drenSheet->setTitle('TIRAGE_DREN');
            $drenSheet->fromArray([
                ['SYNTHESE TIRAGE PAR DREN'],
                ['DREN', 'CENTRES', 'SALLES', 'MARGE', 'EXEMPLAIRES', 'FEUILLES', 'IMPRESSIONS'],
            ], null, 'A1');

            $drenRowIndex = 3;
            foreach (collect($payload['drenRows']) as $row) {
                $drenSheet->fromArray([[
                    $row['dren'],
                    (int) $row['total_centres'],
                    (int) $row['total_salles'],
                    (int) $row['total_margin_surplus'],
                    (int) $row['total_exemplaires'],
                    (int) $row['total_feuilles'],
                    (int) $row['total_impressions'],
                ]], null, "A{$drenRowIndex}");
                $drenRowIndex++;
            }

            $drenSheet->fromArray([[
                'TOTAL',
                (int) collect($payload['drenRows'])->sum('total_centres'),
                (int) collect($payload['drenRows'])->sum('total_salles'),
                (int) collect($payload['drenRows'])->sum('total_margin_surplus'),
                (int) collect($payload['drenRows'])->sum('total_exemplaires'),
                (int) collect($payload['drenRows'])->sum('total_feuilles'),
                (int) collect($payload['drenRows'])->sum('total_impressions'),
            ]], null, "A{$drenRowIndex}");

            $drenSheet->mergeCells('A1:G1');
            $drenSheet->getStyle('A1:G1')->getFont()->setBold(true);
            $drenSheet->getStyle('A2:G2')->getFont()->setBold(true);
            $drenSheet->getStyle('A2:G2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');
            $drenSheet->getStyle("A2:G{$drenRowIndex}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $drenSheet->getStyle("A{$drenRowIndex}:G{$drenRowIndex}")->getFont()->setBold(true);
            foreach (range('A', 'G') as $col) {
                $drenSheet->getColumnDimension($col)->setAutoSize(true);
            }
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'tirage_'.strtolower($printMode).'_'.($filters['annee'] !== '' ? $filters['annee'].'_' : '').'export.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function buildSubjectPrintPayload(Request $request): array
    {
        [$rows, $filters, $annees, $drens, $ciscos] = $this->getFilteredRows($request);
        $settings = $this->getGlobalSettings();
        $printMode = strtolower((string) $request->query('print_mode', 'sord'));
        if (! in_array($printMode, ['sord', 'a4'], true)) {
            $printMode = 'sord';
        }
        $marginPerRoom = max(0, (int) $request->integer('sord_margin_per_room', 5));
        $subjectFilter = trim((string) $request->query('subject', ''));
        $filters['print_mode'] = $printMode;
        $filters['sord_margin_per_room'] = $marginPerRoom;
        $filters['subject'] = $subjectFilter;

        $subjectOptions = collect($this->getConfiguredSubjects(
            $filters['type_examen'] === self::TYPE_ALL ? self::TYPE_BEPC : $filters['type_examen'],
            $settings
        ))->pluck('label', 'key');

        if ($filters['type_examen'] === self::TYPE_ALL) {
            $subjectOptions = collect([
                ...$this->getConfiguredSubjects(self::TYPE_BEPC, $settings),
                ...$this->getConfiguredSubjects(self::TYPE_CEPE, $settings),
            ])->mapWithKeys(fn (array $subject) => [$subject['key'] => $subject['label']]);
        }

        if ($subjectFilter !== '' && ! $subjectOptions->has($subjectFilter)) {
            $subjectFilter = '';
            $filters['subject'] = '';
        }

        $centreRows = $this->buildSubjectPrintRows($rows, $filters['type_examen'], $settings, $printMode, $marginPerRoom);
        $drenRows = $centreRows
            ->groupBy('dren')
            ->map(function (Collection $group, string $dren) use ($subjectFilter, $settings, $filters) {
                $subjects = $group
                    ->flatMap(fn (array $row) => $row['subjects'])
                    ->groupBy('subject_key')
                    ->map(function (Collection $subjectRows) use ($settings, $filters) {
                        $first = $subjectRows->first();
                        $segments = $subjectRows
                            ->flatMap(fn (array $row) => $row['segments'] ?? [])
                            ->groupBy('pages')
                            ->map(function (Collection $segmentRows, int|string $pages) {
                                $firstSegment = $segmentRows->first();

                                return [
                                    'pages' => (int) $pages,
                                    'copies_per_sheet' => (int) ($firstSegment['copies_per_sheet'] ?? 1),
                                    'feuilles' => (int) $segmentRows->sum('feuilles'),
                                ];
                            })
                            ->sortByDesc('pages')
                            ->values();

                        return [
                            'subject_key' => $first['subject_key'],
                            'label' => $first['label'],
                            'order_index' => $this->getSubjectOrderIndex($filters['type_examen'] === self::TYPE_ALL ? self::TYPE_BEPC : $filters['type_examen'], (string) $first['subject_key'], $settings),
                            'pages' => (int) $first['pages'],
                            'room_count' => (int) $subjectRows->sum('room_count'),
                            'candidates' => (int) $subjectRows->sum('candidates'),
                            'margin_surplus' => (int) $subjectRows->sum('margin_surplus'),
                            'exemplaires' => (int) $subjectRows->sum('exemplaires'),
                            'feuilles' => (int) $subjectRows->sum('feuilles'),
                            'impressions' => (int) $subjectRows->sum('impressions'),
                            'copies_per_sheet' => (int) $first['copies_per_sheet'],
                            'segments' => $segments->all(),
                        ];
                    })
                    ->sortBy('order_index')
                    ->values();

                if ($subjectFilter !== '') {
                    $subjects = $subjects
                        ->filter(fn (array $subject) => $subject['subject_key'] === $subjectFilter)
                        ->values();
                }

                return [
                    'dren' => $dren,
                    'total_centres' => (int) $group->count(),
                    'total_salles' => (int) $subjects->sum('room_count'),
                    'total_margin_surplus' => (int) $subjects->sum('margin_surplus'),
                    'total_exemplaires' => (int) $subjects->sum('exemplaires'),
                    'total_feuilles' => (int) $subjects->sum('feuilles'),
                    'total_impressions' => (int) $subjects->sum('impressions'),
                    'subjects' => $subjects->all(),
                ];
            })
            ->sortBy('dren')
            ->values();

        $subjectTotals = $centreRows
            ->flatMap(fn (array $row) => $row['subjects'])
            ->groupBy('subject_key')
            ->map(function (Collection $subjectRows) use ($settings, $filters) {
                $first = $subjectRows->first();
                $segments = $subjectRows
                    ->flatMap(fn (array $row) => $row['segments'] ?? [])
                    ->groupBy('pages')
                    ->map(function (Collection $segmentRows, int|string $pages) {
                        $firstSegment = $segmentRows->first();

                        return [
                            'pages' => (int) $pages,
                            'copies_per_sheet' => (int) ($firstSegment['copies_per_sheet'] ?? 1),
                            'feuilles' => (int) $segmentRows->sum('feuilles'),
                        ];
                    })
                    ->sortByDesc('pages')
                    ->values();

                return [
                    'subject_key' => $first['subject_key'],
                    'label' => $first['label'],
                    'order_index' => $this->getSubjectOrderIndex($filters['type_examen'] === self::TYPE_ALL ? self::TYPE_BEPC : $filters['type_examen'], (string) $first['subject_key'], $settings),
                    'pages' => (int) $first['pages'],
                    'total_room_count' => (int) $subjectRows->sum('room_count'),
                    'total_candidates' => (int) $subjectRows->sum('candidates'),
                    'total_margin_surplus' => (int) $subjectRows->sum('margin_surplus'),
                    'total_exemplaires' => (int) $subjectRows->sum('exemplaires'),
                    'total_feuilles' => (int) $subjectRows->sum('feuilles'),
                    'total_impressions' => (int) $subjectRows->sum('impressions'),
                    'segments' => $segments->all(),
                ];
            })
            ->sortBy('order_index')
            ->values();

        if ($subjectFilter !== '') {
            $subjectTotals = $subjectTotals
                ->filter(fn (array $subject) => $subject['subject_key'] === $subjectFilter)
                ->values();
            $drenRows = $drenRows
                ->map(function (array $row) use ($subjectFilter) {
                    $subjects = collect($row['subjects'])
                        ->filter(fn (array $subject) => $subject['subject_key'] === $subjectFilter)
                        ->values();
                    $row['subjects'] = $subjects->all();
                    $row['total_salles'] = (int) $subjects->sum('room_count');
                    $row['total_margin_surplus'] = (int) $subjects->sum('margin_surplus');
                    $row['total_exemplaires'] = (int) $subjects->sum('exemplaires');
                    $row['total_feuilles'] = (int) $subjects->sum('feuilles');
                    $row['total_impressions'] = (int) $subjects->sum('impressions');

                    return $row;
                })
                ->filter(fn (array $row) => $row['total_exemplaires'] > 0 || $row['total_feuilles'] > 0)
                ->values();
        }

        return [
            'filters' => $filters,
            'annees' => $annees,
            'drens' => $drens,
            'ciscos' => $ciscos,
            'rows' => $centreRows,
            'drenRows' => $drenRows,
            'subjectTotals' => $subjectTotals,
            'subjectOptions' => $subjectOptions,
            'settings' => $settings,
            'printMode' => $printMode,
            'globalStats' => [
                'total_centres' => $centreRows->count(),
                'total_drens' => $drenRows->count(),
                'total_salles' => (int) $subjectTotals->sum('total_room_count'),
                'total_margin_surplus' => (int) $subjectTotals->sum('total_margin_surplus'),
                'total_exemplaires' => (int) $subjectTotals->sum('total_exemplaires'),
                'total_feuilles' => (int) $subjectTotals->sum('total_feuilles'),
                'total_impressions' => (int) $subjectTotals->sum('total_impressions'),
            ],
        ];
    }

    public function vacations(Request $request)
    {
        [$rows, $filters, $annees, $drens] = $this->getFilteredRows($request);

        $centres = $rows
            ->groupBy(fn ($row) => implode('|', [$row->centre_ecrit_id, $row->annee, $row->type_examen]))
            ->map(function (Collection $centreRows) {
                $first = $centreRows->first();
                $candidats = (int) $centreRows->sum('effectif');
                $salles = $this->countDistinctSalles($centreRows);
                $secretaireAvant = 2 + (int) ceil($candidats / 250);
                $surveillantCour = 2 + (int) max(0, ceil(max(0, $salles - 10) / 5));
                $surveillanceSession = (2 * $salles) + $salles + $surveillantCour + 6;
                $correction = 5 + (int) ceil($candidats / 200);

                return [
                    'dren' => $first->dren,
                    'cisco' => $first->cisco,
                    'centre_correction' => $first->centre_correction,
                    'centre_ecrit' => $first->centre_ecrit,
                    'annee' => $first->annee,
                    'type_examen' => $first->type_examen,
                    'candidats' => $candidats,
                    'salles' => $salles,
                    'propositions' => [
                        'avant_session' => $secretaireAvant,
                        'pendant_session' => $surveillanceSession,
                        'apres_session' => $correction,
                        'total' => $secretaireAvant + $surveillanceSession + $correction,
                    ],
                ];
            })
            ->values();

        $ciscos = $centres
            ->groupBy(fn (array $centre) => $centre['dren'].'|'.$centre['cisco'])
            ->map(function (Collection $ciscoCentres) {
                $first = $ciscoCentres->first();
                $candidats = (int) $ciscoCentres->sum('candidats');
                $salles = (int) $ciscoCentres->sum('salles');
                $centresCount = $ciscoCentres->count();
                $organisation = 3 + (int) ceil($candidats / 1000);

                return [
                    'dren' => $first['dren'],
                    'cisco' => $first['cisco'],
                    'candidats' => $candidats,
                    'salles' => $salles,
                    'centres' => $centresCount,
                    'proposition' => $organisation,
                ];
            })
            ->values();

        $drensRecap = $ciscos
            ->groupBy('dren')
            ->map(function (Collection $drenCiscos, string $dren) {
                $ciscoCount = $drenCiscos->count();
                $organisation = 3 + (2 * $ciscoCount);
                $supervision = 2 * $ciscoCount;

                return [
                    'dren' => $dren,
                    'ciscos' => $ciscoCount,
                    'candidats' => (int) $drenCiscos->sum('candidats'),
                    'salles' => (int) $drenCiscos->sum('salles'),
                    'centres' => (int) $drenCiscos->sum('centres'),
                    'propositions' => [
                        'organisation_generale' => $organisation,
                        'supervision_session' => $supervision,
                        'total' => $organisation + $supervision,
                    ],
                ];
            })
            ->values();

        $drenCount = $drensRecap->count();
        $ciscoCount = $ciscos->count();
        $centreCount = $centres->count();
        $centreCorrectionCount = $rows->pluck('centre_correction')->unique()->count();
        $totalCandidats = (int) $centres->sum('candidats');
        $totalSalles = (int) $centres->sum('salles');

        $ciscoTranchesMille = (int) $ciscos->sum(fn (array $row) => (int) ceil(((int) $row['candidats']) / 1000));
        $secretaireAvantCentre = (int) $centres->sum(fn (array $row) => 2 + (int) ceil(((int) $row['candidats']) / 250));
        $secretaireAvantCentre300 = (int) $centres->sum(fn (array $row) => 2 + (int) ceil(((int) $row['candidats']) / 300));
        $surveillantCour = (int) $centres->sum(fn (array $row) => 2 + (int) max(0, ceil(max(0, ((int) $row['salles']) - 10) / 5)));
        $agentPar200 = (int) $centres->sum(fn (array $row) => (int) ceil(((int) $row['candidats']) / 200));
        $tranchesMilleGlobal = (int) ceil(max(1, $totalCandidats) / 1000);

        $centralActivities = collect([
            ['examen' => 'CEPE', 'activite' => "Finalisation des sujets", 'regle' => 'Nombre fixe décret', 'agents' => 3],
            ['examen' => 'CEPE', 'activite' => 'Validation des sujets', 'regle' => 'Nombre fixe décret', 'agents' => 3],
            ['examen' => 'CEPE', 'activite' => 'Testing des sujets', 'regle' => 'Nombre fixe décret', 'agents' => 3],
            ['examen' => 'CEPE', 'activite' => 'Choix des sujets', 'regle' => 'Nombre fixe décret', 'agents' => 50],
            ['examen' => 'CEPE', 'activite' => 'Retouche', 'regle' => 'Nombre fixe décret', 'agents' => 50],
            ['examen' => 'CEPE', 'activite' => 'Préparation enveloppes', 'regle' => 'Nombre fixe décret', 'agents' => 200],
            ['examen' => 'CEPE', 'activite' => 'Impression / sous-pli', 'regle' => 'Nombre fixe décret', 'agents' => 240],
            ['examen' => 'CEPE', 'activite' => 'Dispatching des sujets', 'regle' => 'Nombre fixe décret', 'agents' => 80],
            ['examen' => 'CEPE', 'activite' => 'Traitement données / publication', 'regle' => 'Nombre fixe décret', 'agents' => 50],
            ['examen' => 'CEPE', 'activite' => 'Supervision session/correction', 'regle' => 'Nombre fixe décret', 'agents' => 100],
            ['examen' => 'BEPC', 'activite' => 'Répartition/envoi feuilles', 'regle' => 'Nombre fixe décret', 'agents' => 135],
            ['examen' => 'BEPC', 'activite' => 'Supervision sélection régionale', 'regle' => 'Nombre fixe décret', 'agents' => 50],
            ['examen' => 'BEPC', 'activite' => 'Sélection sujets national', 'regle' => 'Nombre fixe décret', 'agents' => 125],
            ['examen' => 'BEPC', 'activite' => 'Choix des sujets', 'regle' => 'Nombre fixe décret', 'agents' => 50],
            ['examen' => 'BEPC', 'activite' => 'Préparation livres/enveloppes', 'regle' => 'Nombre fixe décret', 'agents' => 180],
            ['examen' => 'BEPC', 'activite' => 'Impression / sous-pli', 'regle' => 'Nombre fixe décret', 'agents' => 300],
            ['examen' => 'BEPC', 'activite' => 'Dispatching des sujets', 'regle' => 'Nombre fixe décret', 'agents' => 80],
            ['examen' => 'BEPC', 'activite' => 'Traitement données / publication', 'regle' => 'Nombre fixe décret', 'agents' => 50],
            ['examen' => 'BEPC', 'activite' => 'Supervision session/correction', 'regle' => 'Nombre fixe décret', 'agents' => 150],
            ['examen' => 'CAE/CAP', 'activite' => 'Préparation enveloppes', 'regle' => 'Nombre fixe décret', 'agents' => 100],
            ['examen' => 'CAE/CAP', 'activite' => 'Sélection sujets', 'regle' => 'Nombre fixe décret', 'agents' => 100],
            ['examen' => 'CAE/CAP', 'activite' => 'Choix des sujets', 'regle' => 'Nombre fixe décret', 'agents' => 50],
            ['examen' => 'CAE/CAP', 'activite' => 'Impression / sous-pli', 'regle' => 'Nombre fixe décret', 'agents' => 100],
        ]);

        $drenActivities = collect([
            ['bloc' => 'CEPE/BEPC', 'activite' => 'Organisation générale DREN', 'regle' => '3 fixes par DREN + 2 par CISCO', 'agents' => (3 * $drenCount) + (2 * $ciscoCount)],
            ['bloc' => 'CEPE/BEPC', 'activite' => 'Supervision session/correction/transcription', 'regle' => '2 agents DREN par CISCO', 'agents' => 2 * $ciscoCount],
            ['bloc' => 'EPS (BEPC)', 'activite' => 'Organisation EPS DREN', 'regle' => '2 cadres DREN fixes + 2 agents par CISCO', 'agents' => (2 * $drenCount) + (2 * $ciscoCount)],
            ['bloc' => 'EPS (BEPC)', 'activite' => 'Suivi et contrôle EPS', 'regle' => '2 cadres DREN fixes + 2 agents par CISCO', 'agents' => (2 * $drenCount) + (2 * $ciscoCount)],
            ['bloc' => 'CAE', 'activite' => 'Supervision session écrite', 'regle' => '2 agents DREN par centre écrit', 'agents' => 2 * $centreCount],
            ['bloc' => 'CAP', 'activite' => 'Supervision session écrite', 'regle' => '2 agents DREN par centre écrit', 'agents' => 2 * $centreCount],
        ]);

        $ciscoActivities = collect([
            ['bloc' => 'CEPE', 'activite' => 'Organisation générale CISCO', 'regle' => '3 fixes par CISCO + 1 par tranche 1000 candidats (par CISCO)', 'agents' => (3 * $ciscoCount) + $ciscoTranchesMille],
            ['bloc' => 'CEPE', 'activite' => 'Suivi et contrôle', 'regle' => '3 fixes par CISCO + 1 par tranche 1000 candidats (par CISCO)', 'agents' => (3 * $ciscoCount) + $ciscoTranchesMille],
            ['bloc' => 'EPS (BEPC)', 'activite' => 'Organisation EPS CISCO', 'regle' => '2 cadres fixes par CISCO + 1 par tranche 1000 candidats (par CISCO)', 'agents' => (2 * $ciscoCount) + $ciscoTranchesMille],
            ['bloc' => 'EPS (BEPC)', 'activite' => 'Suivi EPS CISCO', 'regle' => '2 cadres fixes par CISCO + 1 par tranche 1000 candidats (par CISCO)', 'agents' => (2 * $ciscoCount) + $ciscoTranchesMille],
        ]);

        $centreActivities = collect([
            ['bloc' => 'CEPE/BEPC', 'activite' => 'Secrétariat avant session', 'regle' => '2 fixes/centre + 1 agent/250 candidats', 'agents' => $secretaireAvantCentre],
            ['bloc' => 'CEPE/BEPC', 'activite' => 'Secrétaires en salle', 'regle' => '1 par salle', 'agents' => $totalSalles],
            ['bloc' => 'CEPE/BEPC', 'activite' => 'Surveillants de salle', 'regle' => '2 par salle', 'agents' => 2 * $totalSalles],
            ['bloc' => 'CEPE/BEPC', 'activite' => 'Surveillants de cour', 'regle' => '2 de base + 1/5 salles au-delà de 10 (par centre)', 'agents' => $surveillantCour],
            ['bloc' => 'CAE/CAP', 'activite' => 'Secrétariat avant session', 'regle' => '2 fixes/centre + 1 agent/300 candidats', 'agents' => $secretaireAvantCentre300],
        ]);

        $correctionTranscriptionActivities = collect([
            ['bloc' => 'Correction', 'activite' => 'Secrétariat de correction', 'regle' => '5 fixes/centre correction + 1 agent/200 candidats', 'agents' => (5 * $centreCorrectionCount) + $agentPar200],
            ['bloc' => 'Correction', 'activite' => 'Coordonnateurs', 'regle' => '7 par centre de correction', 'agents' => 7 * $centreCorrectionCount],
            ['bloc' => 'Transcription BEPC', 'activite' => 'Equipe transcription', 'regle' => '19 agents par tranche de 1000 candidats', 'agents' => 19 * $tranchesMilleGlobal],
            ['bloc' => 'Transcription CEPE', 'activite' => 'Equipe transcription', 'regle' => '12 agents par tranche de 1000 candidats', 'agents' => 12 * $tranchesMilleGlobal],
        ]);

        return view('repartition.vacations', [
            'filters' => $filters,
            'annees' => $annees,
            'drens' => $drens,
            'drenRows' => $drensRecap,
            'ciscoRows' => $ciscos,
            'centreRows' => $centres,
            'globalStats' => [
                'total_candidats' => $centres->sum('candidats'),
                'total_salles' => $centres->sum('salles'),
                'total_centres' => $centres->count(),
                'total_drens' => $drenCount,
                'total_ciscos' => $ciscoCount,
                'total_centres_correction' => $centreCorrectionCount,
                'proposition_dren' => $drensRecap->sum(fn (array $row) => $row['propositions']['total']),
                'proposition_cisco' => $ciscos->sum('proposition'),
                'proposition_centres' => $centres->sum(fn (array $row) => $row['propositions']['total']),
            ],
            'centralActivities' => $centralActivities,
            'drenActivities' => $drenActivities,
            'ciscoActivities' => $ciscoActivities,
            'centreActivities' => $centreActivities,
            'correctionTranscriptionActivities' => $correctionTranscriptionActivities,
        ]);
    }

    public function groupes(Request $request)
    {
        [$rows, $filters, $annees, $drens] = $this->getFilteredRows($request);

        $groupCount = (int) $request->query('groups', 5);
        if (! in_array($groupCount, [4, 5], true)) {
            $groupCount = 5;
        }

        $drenStats = $this->buildDrenGroupStats($rows);

        $scenarioCandidates = $this->buildBalancedGroups($drenStats, $groupCount, 'candidats');
        $scenarioCandidatesWithCisco = $this->buildBalancedGroupsWithCiscoAdjustments($drenStats, $groupCount, 'candidats');
        $scenarioSalles = $this->buildBalancedGroups($drenStats, $groupCount, 'salles');

        return view('repartition.groupes', [
            'filters' => $filters,
            'annees' => $annees,
            'drens' => $drens,
            'groupCount' => $groupCount,
            'drenStats' => $drenStats,
            'scenarioCandidates' => $scenarioCandidates,
            'scenarioCandidatesWithCisco' => $scenarioCandidatesWithCisco,
            'scenarioSalles' => $scenarioSalles,
            'globalStats' => [
                'total_candidats' => (int) $drenStats->sum('candidats'),
                'total_salles' => (int) $drenStats->sum('salles'),
                'total_drens' => (int) $drenStats->count(),
                'total_ciscos' => (int) $drenStats->sum(fn (array $dren) => count($dren['ciscos'])),
            ],
        ]);
    }

    public function exportExcel(Request $request)
    {
        [$rows, $filters] = $this->getFilteredRows($request);

        $centreRows = $this->buildStatsExportRows($rows, $filters['type_examen']);
        [$headers, $numericKeys] = $this->getStatsExportMeta($filters['type_examen']);

        $csvRows = [$headers];
        $grandTotal = array_fill_keys($numericKeys, 0);
        $soubiqueCentreSeen = [];

        foreach ($centreRows as $line) {
            $lineForExport = $line;
            $centreKey = implode('|', [
                $line['dren'],
                $line['cisco'],
                $line['centre_correction'],
                $line['centre_ecrit'],
                $line['type_examen'],
            ]);
            if (isset($soubiqueCentreSeen[$centreKey])) {
                $lineForExport['soubique_sujets'] = 0;
            } else {
                $soubiqueCentreSeen[$centreKey] = true;
            }

            $csvRows[] = $this->lineToStatsCsvRow($lineForExport, $filters['type_examen']);

            foreach ($numericKeys as $key) {
                $grandTotal[$key] += $lineForExport[$key];
            }
        }

        $csvRows[] = $this->buildStatsTotalRow('TOTAL GLOBAL', $grandTotal, $filters['type_examen']);

        $spreadsheet = new Spreadsheet();
        $detailSheet = $spreadsheet->getActiveSheet();
        $detailSheet->setTitle('DETAIL');

        $detailRow = 1;
        foreach ($csvRows as $line) {
            $detailSheet->fromArray($line, null, "A{$detailRow}");
            $detailRow++;
        }
        $detailLastCol = $detailSheet->getHighestColumn();
        $detailSheet->getStyle("A1:{$detailLastCol}1")->getFont()->setBold(true);
        $detailSheet->getStyle("A1:{$detailLastCol}1")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');
        $detailLastColIndex = Coordinate::columnIndexFromString($detailLastCol);
        for ($i = 1; $i <= $detailLastColIndex; $i++) {
            $detailSheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }

        $recapSheet = $spreadsheet->createSheet();
        $recapSheet->setTitle('RECAP_CENTRES');
        $settings = $this->getGlobalSettings();
        $soubiqueByCentre = $this->buildSubjectSoubiqueRows($rows, $filters['type_examen'], $settings)
            ->keyBy(fn (array $row) => implode('|', [
                $row['dren'],
                $row['cisco'],
                $row['centre_correction'],
                $row['centre_ecrit'],
                $row['type_examen'],
            ]));
        $recapHeaders = ['DREN', 'CISCO', 'CENTRE CORRECTION', 'CENTRE ECRIT', 'ALL', 'ESP', 'ANG', 'B', 'ETRANGER OPTION A', 'ETRANGER OPTION B', 'SALLE', 'SOUBIQUE SUJETS', 'TOTAL'];
        $recapSheet->fromArray($recapHeaders, null, 'A1');

        $centreRecapRows = $rows
            ->groupBy(fn ($row) => implode('|', [$row->dren, $row->cisco, $row->centre_correction, $row->centre_ecrit]))
            ->map(function (Collection $group) {
                $sum = fn (string $langue): int => (int) $group->where('langue', $langue)->sum('effectif');

                return [
                    'dren' => (string) $group->first()->dren,
                    'cisco' => (string) $group->first()->cisco,
                    'centre_correction' => (string) $group->first()->centre_correction,
                    'centre_ecrit' => (string) $group->first()->centre_ecrit,
                    'all' => $sum('Allemand'),
                    'esp' => $sum('Esp'),
                    'ang' => $sum('Anglais'),
                    'b' => $sum('Option B'),
                    'etranger_option_a' => $this->sumForeignOption($group, 'A'),
                    'etranger_option_b' => $this->sumForeignOption($group, 'B'),
                    'salle' => $this->countDistinctSalles($group),
                    'type_examen' => (string) $group->first()->type_examen,
                    'total' => (int) $group->sum('effectif'),
                ];
            })
            ->sortBy(['dren', 'cisco', 'centre_correction', 'centre_ecrit'])
            ->values();

        $recapRow = 2;
        foreach ($centreRecapRows as $line) {
            $centreKey = implode('|', [
                $line['dren'],
                $line['cisco'],
                $line['centre_correction'],
                $line['centre_ecrit'],
                $line['type_examen'],
            ]);
            $recapSheet->fromArray([
                $line['dren'],
                $line['cisco'],
                $line['centre_correction'],
                $line['centre_ecrit'],
                $line['all'],
                $line['esp'],
                $line['ang'],
                $line['b'],
                $line['etranger_option_a'],
                $line['etranger_option_b'],
                $line['salle'],
                (int) ($soubiqueByCentre->get($centreKey)['soubique_sujets'] ?? 0),
                $line['total'],
            ], null, "A{$recapRow}");
            $recapRow++;
        }
        $recapSheet->getStyle('A1:M1')->getFont()->setBold(true);
        $recapSheet->getStyle('A1:M1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');
        $recapTableLastRow = max(1, $recapRow - 1);
        $recapSheet->getStyle("A1:M{$recapTableLastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        foreach (range('A', 'M') as $col) {
            $recapSheet->getColumnDimension($col)->setAutoSize(true);
        }

        $this->addCiscoRecapSheet($spreadsheet, $rows);

        $spreadsheet->setActiveSheetIndex(0);
        $writer = new Xlsx($spreadsheet);
        $fileName = 'recap_repartition_'.($filters['annee'] !== '' ? $filters['annee'].'_' : '').strtolower($filters['type_examen']).'.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function dispatchingPreview(Request $request)
    {
        [$rows, $filters, $annees, $drens] = $this->getFilteredRows($request);
        $dispatchRows = $this->buildDispatchingRows($rows);
        $dispatchingOrder = $this->buildDispatchingPointOrder($request, $dispatchRows);
        $dispatchRows = $this->sortDispatchingRowsByPointOrder($dispatchRows, $dispatchingOrder);

        return view('repartition.dispatching-preview', [
            'filters' => $filters,
            'annees' => $annees,
            'drens' => $drens,
            'dispatchingOrder' => $dispatchingOrder,
            'dispatchingByAxe' => $dispatchRows->groupBy('axe_dispatching'),
            'globalStats' => [
                'total_centres' => $dispatchRows->count(),
                'total_candidats' => (int) $dispatchRows->sum('candidats'),
                'total_salles' => (int) $dispatchRows->sum('salles'),
                'total_axes' => $dispatchRows->pluck('axe_dispatching')->unique()->count(),
                'total_points' => $dispatchRows->pluck('point_largage')->unique()->count(),
            ],
        ]);
    }

    public function exportDispatchingExcel(Request $request)
    {
        [$rows, $filters] = $this->getFilteredRows($request);
        $dispatchRows = $this->buildDispatchingRows($rows);
        $dispatchingOrder = $this->buildDispatchingPointOrder($request, $dispatchRows);
        $spreadsheet = new Spreadsheet();

        $usedSheetNames = [];
        if ($dispatchRows->isEmpty()) {
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('DISPATCHING');
            $sheet->setCellValue('A1', 'Aucune donnée pour les filtres sélectionnés.');
        }

        $centresByAxe = $this->sortDispatchingRowsByPointOrder($dispatchRows, $dispatchingOrder)
            ->groupBy('axe_dispatching')
            ->map(fn (Collection $axeRows) => $axeRows
                ->groupBy('point_largage')
                ->map(fn (Collection $pointRows) => $pointRows->map(fn (array $row) => [
                    'cisco' => $row['cisco'],
                    'centre_id' => null,
                    'centre_nom' => $row['centre_ecrit'],
                    'total_salles' => $row['salles'],
                    'total_candidats' => $row['candidats'],
                ])->values()));

        $sheetIndex = 0;
        foreach ($centresByAxe as $axe => $points) {
            $sheet = $sheetIndex === 0
                ? $spreadsheet->getActiveSheet()
                : $spreadsheet->createSheet();
            $sheetIndex++;
            $sheetName = $this->makeExcelSheetName($axe, $usedSheetNames);
            $sheet->setTitle($sheetName);

            // Set default row height to 20
            $sheet->getDefaultRowDimension()->setRowHeight(20);

            // Title
            $sheet->setCellValue('A1', 'DISPATCHING');
            $sheet->mergeCells('A1:G1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $rowIndex = 3;
            $numero = 1; // Numérotation par axe

            foreach ($points as $point => $centres) {
                // Point de largage header
                $sheet->setCellValue("A{$rowIndex}", "POINT DE LARGAGE: {$point}");
                $sheet->mergeCells("A{$rowIndex}:G{$rowIndex}");
                $sheet->getStyle("A{$rowIndex}")->getFont()->setBold(true);
                $sheet->getStyle("A{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');
                $rowIndex++;

                // Header for centres
                $sheet->fromArray([
                    ['CISCO', 'N°', 'Nom du centre', 'total salle', 'total candidats', 'nombre de soubiques', 'Observation']
                ], null, "A{$rowIndex}");
                $sheet->getStyle("A{$rowIndex}:G{$rowIndex}")->getFont()->setBold(true);
                $sheet->getStyle("A{$rowIndex}:G{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');
                $rowIndex++;

                // Data rows
                foreach ($centres as $centre) {
                    $sheet->fromArray([
                        $centre['cisco'],
                        $numero++,
                        $centre['centre_nom'],
                        $centre['total_salles'],
                        $centre['total_candidats'],
                        '', // empty for nombre de soubiques
                        '', // empty for Observation
                    ], null, "A{$rowIndex}");
                    $rowIndex++;
                }

                // Total for this point de largage (sans tableau)
                $totalCentres = count($centres);
                $sheet->setCellValue("A{$rowIndex}", "ARRÊTAGE POINT {$point}: {$totalCentres} centres");
                $sheet->mergeCells("A{$rowIndex}:G{$rowIndex}");
                $sheet->getStyle("A{$rowIndex}")->getFont()->setBold(true);
                $sheet->getStyle("A{$rowIndex}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $rowIndex++;
                $rowIndex++; // Empty row
            }

            // Total général (sans tableau)
            $totalCentresGeneral = $points->flatten(1)->count();
            $sheet->setCellValue("A{$rowIndex}", "ARRÊTAGE TOTAL: {$totalCentresGeneral} centres");
            $sheet->mergeCells("A{$rowIndex}:G{$rowIndex}");
            $sheet->getStyle("A{$rowIndex}")->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle("A{$rowIndex}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $rowIndex++;
            $rowIndex++; // Empty row

            // Fields for remettant, police, convoyeur on one line
            $sheet->setCellValue("A{$rowIndex}", "Remettant:");
            $sheet->setCellValue("C{$rowIndex}", "");
            $sheet->setCellValue("D{$rowIndex}", "Police:");
            $sheet->setCellValue("F{$rowIndex}", "");
            $sheet->setCellValue("G{$rowIndex}", "Convoyeur:");
            $sheet->setCellValue("H{$rowIndex}", "");
            $rowIndex++;

            // Axe name at the bottom
            $rowIndex++;
            $sheet->setCellValue("A{$rowIndex}", $axe);
            $sheet->mergeCells("A{$rowIndex}:G{$rowIndex}");
            $sheet->getStyle("A{$rowIndex}")->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle("A{$rowIndex}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Auto size columns
            foreach (range('A', 'H') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Borders only for the data table
            $tableStart = 4; // After title and first point header
            $tableEnd = $rowIndex - 5; // Before totals and signataires
            if ($tableEnd >= $tableStart) {
                $tableRange = "A{$tableStart}:G{$tableEnd}";
                $sheet->getStyle($tableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            }
        }

        $spreadsheet->setActiveSheetIndex(0);
        $writer = new Xlsx($spreadsheet);
        $fileName = 'dispatching_' . ($filters['annee'] !== '' ? $filters['annee'] . '_' : '') . strtolower($filters['type_examen']) . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function languageOptionStats(Request $request)
    {
        [$rows, $filters, $annees, $drens, $ciscos] = $this->getFilteredRows($request);

        // Values are the database labels; display labels are handled by the view.
        $allLanguages = [
            '' => 'Toutes les langues',
            'Anglais' => 'Anglais',
            'Allemand' => 'Allemand',
            'Esp' => 'Espagnol',
            'Option B' => 'Option B',
            'Etranger Option A' => 'Etranger Option A',
            'Etranger Option B' => 'Etranger Option B',
        ];

        // Get selected language from query
        $selectedLanguage = (string) $request->query('langue', '');
        if (! array_key_exists($selectedLanguage, $allLanguages)) {
            $selectedLanguage = '';
        }
        $selectedLanguageLabel = $allLanguages[$selectedLanguage];

        // Get soubique data
        $settings = $this->getGlobalSettings();
        $typeExamen = $filters['type_examen'] === self::TYPE_CEPE ? self::TYPE_CEPE : self::TYPE_BEPC;
        $soubiqueData = $this->buildSubjectSoubiqueRows($rows, $typeExamen, $settings)
            ->keyBy(fn (array $row) => implode('|', [
                $row['dren'],
                $row['cisco'],
                $row['centre_correction'],
                $row['centre_ecrit'],
                $row['type_examen'],
                $row['annee'],
            ]));

        // Count only salles where the selected language has candidates.
        $languageRows = $rows
            ->when($selectedLanguage !== '', fn (Collection $rows) => $rows->filter(fn ($row) => (string) $row->langue === $selectedLanguage))
            ->filter(fn ($row) => (int) $row->effectif > 0);

        $drenFilterActive = $filters['dren'] !== '';

        // Build stats grouped by CISCO (always show per CISCO)
        $stats = $languageRows
            ->groupBy(fn ($row) => $row->dren.'|'.$row->cisco)
            ->map(function (Collection $groupRows) use ($soubiqueData) {
                $first = $groupRows->first();

                // Count salles distinctes by centre, year, exam type, and room number.
                $salleCount = $this->countDistinctSalles($groupRows);

                // Get GE distribution based on type_examen
                $typeExamen = (string) $first->type_examen;
                $geDistribution = $this->getGeDistribution($salleCount, $typeExamen);
                $geTotal = count($geDistribution);
                $geRepartition = $geTotal > 0 ? implode('+', array_map(fn (int $n) => (string) $n, $geDistribution)) : '';

                // Get soubique count
                $centreGroups = $groupRows->groupBy(fn ($row) => $row->centre_ecrit_id.'|'.$row->annee);
                $totalSoubique = $centreGroups->sum(function (Collection $centreGroup) use ($soubiqueData) {
                    $firstCentre = $centreGroup->first();
                    $key = implode('|', [
                        (string) $firstCentre->dren,
                        (string) $firstCentre->cisco,
                        (string) $firstCentre->centre_correction,
                        (string) $firstCentre->centre_ecrit,
                        (string) $firstCentre->type_examen,
                        (string) $firstCentre->annee,
                    ]);
                    return (int) ($soubiqueData->get($key)['soubique_sujets'] ?? 0);
                });

                return [
                    'dren' => (string) $first->dren,
                    'cisco' => (string) $first->cisco,
                    'salle' => $salleCount,
                    'ge_total' => $geTotal,
                    'ge_repartition' => $geRepartition,
                    'soubique' => $totalSoubique,
                ];
            })
            ->sortBy(fn ($item) => [$item['dren'], $item['cisco']])
            ->values();

        // Filter by DREN if active
        if ($drenFilterActive) {
            $stats = $stats->filter(fn ($item) => $item['dren'] === $filters['dren']);
        }

        $statsByDren = $stats
            ->groupBy('dren')
            ->map(function (Collection $ciscoRows, string $dren) {
                return [
                    'dren' => $dren,
                    'ciscos' => $ciscoRows->values(),
                    'totals' => [
                        'salle' => (int) $ciscoRows->sum('salle'),
                        'ge_total' => (int) $ciscoRows->sum('ge_total'),
                        'soubique' => (int) $ciscoRows->sum('soubique'),
                    ],
                ];
            })
            ->values();

        // Calculate totals
        $totals = [
            'salle' => (int) $stats->sum('salle'),
            'ge_total' => (int) $stats->sum('ge_total'),
            'soubique' => (int) $stats->sum('soubique'),
        ];

        return view('repartition.language-option-stats', [
            'filters' => $filters,
            'annees' => $annees,
            'drens' => $drens,
            'ciscos' => $ciscos,
            'allLanguages' => $allLanguages,
            'selectedLanguage' => $selectedLanguage,
            'selectedLanguageLabel' => $selectedLanguageLabel,
            'stats' => $stats,
            'statsByDren' => $statsByDren,
            'totals' => $totals,
        ]);
    }

    private function buildStatsExportRows(Collection $rows, string $typeExamen): Collection
    {
        $settings = $this->getGlobalSettings();
        $soubiqueByCentre = $this->buildSubjectSoubiqueRows($rows, $typeExamen, $settings)
            ->keyBy(fn (array $row) => implode('|', [
                $row['dren'],
                $row['cisco'],
                $row['centre_correction'],
                $row['centre_ecrit'],
                $row['type_examen'],
            ]));

        return $rows
            ->groupBy(fn ($row) => implode('|', [
                $row->dren,
                $row->cisco,
                $row->centre_correction,
                $row->centre_ecrit,
                $row->type_examen,
                $row->numero_salle,
            ]))
            ->map(function (Collection $group) use ($soubiqueByCentre) {
                $first = $group->first();
                $sum = fn (string $langue): int => (int) $group->where('langue', $langue)->sum('effectif');
                $startsWithForeign = function (string $langue, string $prefix): bool {
                    $normalized = mb_strtolower(trim($langue));
                    $prefixNormalized = mb_strtolower($prefix);

                    if (str_starts_with($normalized, $prefixNormalized)) {
                        return true;
                    }

                    $accentPrefix = str_replace('etranger', 'étranger', $prefixNormalized);

                    return str_starts_with($normalized, $accentPrefix);
                };
                $sumStartsWith = fn (string $prefix): int => (int) $group
                    ->filter(fn ($row) => $startsWithForeign((string) $row->langue, $prefix))
                    ->sum('effectif');

                $anglais = $sum('Anglais');
                $esp = $sum('Esp');
                $allemand = $sum('Allemand');
                $optionB = $sum('Option B');
                $etrangerOptionA = $sumStartsWith('Etranger Option A');
                $etrangerOptionB = $sumStartsWith('Etranger Option B');
                $totalCepe = $sum(self::CEPE_KEY);
                $centreKey = implode('|', [
                    $first->dren,
                    $first->cisco,
                    $first->centre_correction,
                    $first->centre_ecrit,
                    $first->type_examen,
                ]);
                $soubiqueSujets = (int) ($soubiqueByCentre->get($centreKey)['soubique_sujets'] ?? 0);

                return [
                    'dren' => $first->dren,
                    'cisco' => $first->cisco,
                    'centre_correction' => $first->centre_correction,
                    'centre_ecrit' => $first->centre_ecrit,
                    'type_examen' => $first->type_examen,
                    'numero_salle' => (int) $first->numero_salle,
                    'anglais' => $anglais,
                    'esp' => $esp,
                    'allemand' => $allemand,
                    'option_b' => $optionB,
                    'etranger_option_a' => $etrangerOptionA,
                    'etranger_option_b' => $etrangerOptionB,
                    'total_cepe' => $totalCepe,
                    'soubique_sujets' => $soubiqueSujets,
                    'total' => $anglais + $esp + $allemand + $optionB + $etrangerOptionA + $etrangerOptionB + $totalCepe,
                ];
            })
            ->sortBy(['dren', 'cisco', 'centre_correction', 'centre_ecrit', 'numero_salle'])
            ->values();
    }

    private function getStatsExportMeta(string $typeExamen): array
    {
        if ($typeExamen === self::TYPE_CEPE) {
            return [[
                'DREN', 'CISCO', 'CENTRE CORRECTION', 'CENTRE ECRIT', 'SALLE', 'TOTAL CEPE', 'SOUBIQUE SUJETS',
            ], ['total_cepe', 'soubique_sujets']];
        }

        if ($typeExamen === self::TYPE_BEPC) {
            return [[
                'DREN', 'CISCO', 'CENTRE CORRECTION', 'CENTRE ECRIT', 'SALLE',
                'OPTION A - ANGLAIS', 'OPTION A - ESP', 'OPTION A - ALLEMAND', 'OPTION B',
                'ETRANGER OPTION A', 'ETRANGER OPTION B', 'SOUBIQUE SUJETS', 'TOTAL',
            ], ['anglais', 'esp', 'allemand', 'option_b', 'etranger_option_a', 'etranger_option_b', 'soubique_sujets', 'total']];
        }

        return [[
            'DREN', 'CISCO', 'CENTRE CORRECTION', 'CENTRE ECRIT', 'TYPE EXAMEN', 'SALLE',
            'OPTION A - ANGLAIS', 'OPTION A - ESP', 'OPTION A - ALLEMAND', 'OPTION B',
            'ETRANGER OPTION A', 'ETRANGER OPTION B', 'TOTAL CEPE', 'SOUBIQUE SUJETS', 'TOTAL',
        ], ['anglais', 'esp', 'allemand', 'option_b', 'etranger_option_a', 'etranger_option_b', 'total_cepe', 'soubique_sujets', 'total']];
    }

    private function lineToStatsCsvRow(array $line, string $typeExamen): array
    {
        if ($typeExamen === self::TYPE_CEPE) {
            return [
                $line['dren'],
                $line['cisco'],
                $line['centre_correction'],
                $line['centre_ecrit'],
                $line['numero_salle'],
                $line['total_cepe'],
                $line['soubique_sujets'],
            ];
        }

        if ($typeExamen === self::TYPE_BEPC) {
            return [
                $line['dren'],
                $line['cisco'],
                $line['centre_correction'],
                $line['centre_ecrit'],
                $line['numero_salle'],
                $line['anglais'],
                $line['esp'],
                $line['allemand'],
                $line['option_b'],
                $line['etranger_option_a'],
                $line['etranger_option_b'],
                $line['soubique_sujets'],
                $line['total'],
            ];
        }

        return [
            $line['dren'],
            $line['cisco'],
            $line['centre_correction'],
            $line['centre_ecrit'],
            $line['type_examen'],
            $line['numero_salle'],
            $line['anglais'],
            $line['esp'],
            $line['allemand'],
            $line['option_b'],
            $line['etranger_option_a'],
            $line['etranger_option_b'],
            $line['total_cepe'],
            $line['soubique_sujets'],
            $line['total'],
        ];
    }

    private function buildStatsTotalRow(string $label, array $totals, string $typeExamen): array
    {
        if ($typeExamen === self::TYPE_CEPE) {
            return [$label, '', '', '', '', $totals['total_cepe'] ?? 0, $totals['soubique_sujets'] ?? 0];
        }

        if ($typeExamen === self::TYPE_BEPC) {
            return [
                $label, '', '', '', '',
                $totals['anglais'] ?? 0,
                $totals['esp'] ?? 0,
                $totals['allemand'] ?? 0,
                $totals['option_b'] ?? 0,
                $totals['etranger_option_a'] ?? 0,
                $totals['etranger_option_b'] ?? 0,
                $totals['soubique_sujets'] ?? 0,
                $totals['total'] ?? 0,
            ];
        }

        return [
            $label, '', '', '', '', '',
            $totals['anglais'] ?? 0,
            $totals['esp'] ?? 0,
            $totals['allemand'] ?? 0,
            $totals['option_b'] ?? 0,
            $totals['etranger_option_a'] ?? 0,
            $totals['etranger_option_b'] ?? 0,
            $totals['total_cepe'] ?? 0,
            $totals['soubique_sujets'] ?? 0,
            $totals['total'] ?? 0,
        ];
    }

    private function buildDispatchingRows(Collection $rows): Collection
    {
        return $rows
            ->groupBy(fn ($row) => implode('|', [$row->centre_ecrit_id, $row->annee, $row->type_examen]))
            ->map(function (Collection $group) {
                $first = $group->first();
                $sum = fn (string $langue): int => (int) $group->where('langue', $langue)->sum('effectif');

                return [
                    'dren' => $first->dren,
                    'cisco' => $first->cisco,
                    'centre_correction' => $first->centre_correction,
                    'centre_ecrit' => $first->centre_ecrit,
                    'code_centre' => mb_strtoupper(mb_substr(trim((string) $first->centre_ecrit), 0, 3)),
                    'axe_dispatching' => trim((string) ($first->axe_dispatching ?? '')) ?: 'AXE NON RENSEIGNE',
                    'point_largage' => trim((string) ($first->point_largage ?? '')) ?: 'POINT NON RENSEIGNE',
                    'esp' => $sum('Esp'),
                    'allemand' => $sum('Allemand'),
                    'anglais' => $sum('Anglais'),
                    'option_b' => $sum('Option B'),
                    'candidats' => (int) $group->sum('effectif'),
                    'salles' => $this->countDistinctSalles($group),
                ];
            })
            ->sortBy(['axe_dispatching', 'point_largage', 'dren', 'cisco', 'centre_ecrit'])
            ->values();
    }

    private function buildDispatchingPointOrder(Request $request, Collection $dispatchRows): array
    {
        $axes = $dispatchRows
            ->pluck('axe_dispatching')
            ->unique()
            ->sort(SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $selectedAxe = trim((string) $request->query('order_axe', ''));
        if ($selectedAxe === '' || ! $axes->contains($selectedAxe)) {
            $selectedAxe = (string) ($axes->first() ?? '');
        }

        $points = $selectedAxe !== ''
            ? $dispatchRows
                ->where('axe_dispatching', $selectedAxe)
                ->pluck('point_largage')
                ->unique()
                ->sort(SORT_NATURAL | SORT_FLAG_CASE)
                ->values()
            : collect();

        $requestedPositions = $request->query('point_order_positions', []);
        if (! is_array($requestedPositions)) {
            $requestedPositions = [];
        }

        $positionByPoint = [];
        $orderedPoints = $points
            ->map(function (string $point, int $index) use ($requestedPositions, &$positionByPoint) {
                $rawPosition = $requestedPositions[$point] ?? ($index + 1);
                $position = is_numeric($rawPosition) ? max(1, (int) $rawPosition) : ($index + 1);
                $positionByPoint[$point] = $position;

                return [
                    'point' => $point,
                    'position' => $position,
                    'index' => $index,
                ];
            })
            ->sortBy([
                ['position', 'asc'],
                ['index', 'asc'],
            ])
            ->pluck('point')
            ->values();

        return [
            'axes' => $axes,
            'selected_axe' => $selectedAxe,
            'points' => $points,
            'ordered_points' => $orderedPoints,
            'positions' => $positionByPoint,
        ];
    }

    private function sortDispatchingRowsByPointOrder(Collection $dispatchRows, array $dispatchingOrder): Collection
    {
        $selectedAxe = (string) ($dispatchingOrder['selected_axe'] ?? '');
        $pointRanks = collect($dispatchingOrder['ordered_points'] ?? [])
            ->values()
            ->flip()
            ->map(fn (int $rank) => $rank + 1)
            ->all();

        return $dispatchRows
            ->sort(function (array $left, array $right) use ($selectedAxe, $pointRanks) {
                foreach ([
                    strcmp((string) $left['axe_dispatching'], (string) $right['axe_dispatching']),
                    $this->dispatchingPointRank($left, $selectedAxe, $pointRanks) <=> $this->dispatchingPointRank($right, $selectedAxe, $pointRanks),
                    strcmp((string) $left['point_largage'], (string) $right['point_largage']),
                    strcmp((string) $left['dren'], (string) $right['dren']),
                    strcmp((string) $left['cisco'], (string) $right['cisco']),
                    strcmp((string) $left['centre_ecrit'], (string) $right['centre_ecrit']),
                ] as $comparison) {
                    if ($comparison !== 0) {
                        return $comparison;
                    }
                }

                return 0;
            })
            ->values();
    }

    private function dispatchingPointRank(array $row, string $selectedAxe, array $pointRanks): int
    {
        if ($selectedAxe === '' || (string) $row['axe_dispatching'] !== $selectedAxe) {
            return 999999;
        }

        return (int) ($pointRanks[(string) $row['point_largage']] ?? 999999);
    }

    private function makeExcelSheetName(string $axe, array &$usedSheetNames): string
    {
        $base = trim(preg_replace('/[\[\]\:\*\/\\\\\?]/', ' ', $axe) ?? '');
        if ($base === '') {
            $base = 'AXE';
        }
        $base = mb_substr($base, 0, 31);

        $name = $base;
        $i = 1;
        while (in_array($name, $usedSheetNames, true)) {
            $suffix = ' '.$i;
            $name = mb_substr($base, 0, 31 - mb_strlen($suffix)).$suffix;
            $i++;
        }

        $usedSheetNames[] = $name;

        return $name;
    }

    private function getCentresSaisieStats(array $filters): array
    {
        $buildStatsByType = function (string $type) use ($filters): array {
            $centresQuery = DB::table('centre_ecrits as ce')
                ->join('centre_corrections as cc', 'cc.id', '=', 'ce.centre_correction_id')
                ->join('ciscos as cs', 'cs.id', '=', 'cc.cisco_id')
                ->join('drens as d', 'd.id', '=', 'cs.dren_id')
                ->where('ce.type_examen', $type);

            if ($filters['dren'] !== '') {
                $centresQuery->where('d.nom', $filters['dren']);
            }

            $total = (int) $centresQuery->distinct('ce.id')->count('ce.id');

            $saisisQuery = DB::table('repartition_salles as rs')
                ->join('centre_ecrits as ce', 'ce.id', '=', 'rs.centre_ecrit_id')
                ->join('centre_corrections as cc', 'cc.id', '=', 'ce.centre_correction_id')
                ->join('ciscos as cs', 'cs.id', '=', 'cc.cisco_id')
                ->join('drens as d', 'd.id', '=', 'cs.dren_id')
                ->where('ce.type_examen', $type);

            if ($filters['annee'] !== '') {
                $saisisQuery->where('rs.annee', $filters['annee']);
            }
            if ($filters['dren'] !== '') {
                $saisisQuery->where('d.nom', $filters['dren']);
            }

            if ($type === self::TYPE_CEPE) {
                $saisisQuery->where('rs.langue', self::CEPE_KEY);
            } else {
                $saisisQuery->where('rs.langue', '!=', self::CEPE_KEY);
            }

            $saisis = (int) $saisisQuery->distinct('ce.id')->count('ce.id');

            return [
                'total' => $total,
                'saisis' => $saisis,
                'non_saisis' => max(0, $total - $saisis),
            ];
        };

        $bepc = $buildStatsByType(self::TYPE_BEPC);
        $cepe = $buildStatsByType(self::TYPE_CEPE);

        if (($filters['type_examen'] ?? self::TYPE_ALL) === self::TYPE_BEPC) {
            $summary = $bepc;
        } elseif (($filters['type_examen'] ?? self::TYPE_ALL) === self::TYPE_CEPE) {
            $summary = $cepe;
        } else {
            $summary = [
                'total' => $bepc['total'] + $cepe['total'],
                'saisis' => $bepc['saisis'] + $cepe['saisis'],
                'non_saisis' => $bepc['non_saisis'] + $cepe['non_saisis'],
            ];
        }

        return [
            'total' => $summary['total'],
            'saisis' => $summary['saisis'],
            'non_saisis' => $summary['non_saisis'],
            'by_type' => [
                self::TYPE_BEPC => $bepc,
                self::TYPE_CEPE => $cepe,
            ],
        ];
    }

    private function buildDrenGroupStats(Collection $rows): Collection
    {
        return $rows
            ->groupBy('dren')
            ->map(function (Collection $drenRows, string $dren) {
                $ciscos = $drenRows
                    ->groupBy('cisco')
                    ->map(function (Collection $ciscoRows, string $cisco) use ($dren) {
                        return [
                            'dren' => $dren,
                            'cisco' => $cisco,
                            'candidats' => (int) $ciscoRows->sum('effectif'),
                            'salles' => $this->countDistinctSalles($ciscoRows),
                        ];
                    })
                    ->sortByDesc('candidats')
                    ->values()
                    ->all();

                return [
                    'dren' => $dren,
                    'candidats' => (int) $drenRows->sum('effectif'),
                    'salles' => $this->countDistinctSalles($drenRows),
                    'ciscos' => $ciscos,
                ];
            })
            ->sortByDesc('candidats')
            ->values();
    }

    private function buildBalancedGroups(Collection $drenStats, int $groupCount, string $metric): array
    {
        $groups = $this->makeEmptyGroups($groupCount);
        $items = $drenStats->sortByDesc($metric)->values();

        foreach ($items as $item) {
            $targetIndex = $this->findLightestGroupIndex($groups, $metric);
            $groups[$targetIndex]['items'][] = [
                'type' => 'dren',
                'label' => $item['dren'],
                'dren' => $item['dren'],
                'candidats' => (int) $item['candidats'],
                'salles' => (int) $item['salles'],
                'ciscos' => $item['ciscos'],
            ];
            $groups[$targetIndex]['drens'][] = $item['dren'];
            $groups[$targetIndex]['candidats'] += (int) $item['candidats'];
            $groups[$targetIndex]['salles'] += (int) $item['salles'];
        }

        return $this->finalizeGroupScenario($groups, $metric);
    }

    private function buildBalancedGroupsWithCiscoAdjustments(Collection $drenStats, int $groupCount, string $metric): array
    {
        $groups = $this->makeEmptyGroups($groupCount);
        foreach ($drenStats->sortByDesc($metric)->values() as $item) {
            $targetIndex = $this->findLightestGroupIndex($groups, $metric);
            $groups[$targetIndex]['items'][] = [
                'type' => 'dren',
                'label' => $item['dren'],
                'dren' => $item['dren'],
                'candidats' => (int) $item['candidats'],
                'salles' => (int) $item['salles'],
                'ciscos' => $item['ciscos'],
            ];
            $groups[$targetIndex]['candidats'] += (int) $item['candidats'];
            $groups[$targetIndex]['salles'] += (int) $item['salles'];
        }

        $adjustments = [];

        $maxIterations = max(4, $groupCount * 3);
        for ($iteration = 0; $iteration < $maxIterations; $iteration++) {
            usort($groups, fn (array $a, array $b) => ($b[$metric] <=> $a[$metric]) ?: ($a['group'] <=> $b['group']));

            $heavyIndex = 0;
            $lightIndex = count($groups) - 1;
            $heavy = $groups[$heavyIndex];
            $light = $groups[$lightIndex];

            $spreadBefore = (int) $heavy[$metric] - (int) $light[$metric];
            if ($spreadBefore <= 0) {
                break;
            }

            $bestMove = null;

            foreach ($heavy['items'] as $itemIndex => $item) {
                $ciscos = $item['ciscos'] ?? [];
                if (count($ciscos) <= 1) {
                    continue;
                }

                foreach ($ciscos as $ciscoIndex => $cisco) {
                    $sourceRemaining = (int) $heavy[$metric] - (int) $cisco[$metric];
                    $targetAfter = (int) $light[$metric] + (int) $cisco[$metric];
                    $spreadAfter = max($sourceRemaining, $targetAfter) - min($sourceRemaining, $targetAfter);

                    if ($spreadAfter >= $spreadBefore) {
                        continue;
                    }

                    if ($bestMove === null || $spreadAfter < $bestMove['spread_after']) {
                        $bestMove = [
                            'item_index' => $itemIndex,
                            'cisco_index' => $ciscoIndex,
                            'cisco' => $cisco,
                            'spread_after' => $spreadAfter,
                        ];
                    }
                }
            }

            if ($bestMove === null) {
                break;
            }

            $movedCisco = $bestMove['cisco'];
            array_splice($groups[$heavyIndex]['items'][$bestMove['item_index']]['ciscos'], $bestMove['cisco_index'], 1);
            $groups[$heavyIndex]['items'][$bestMove['item_index']]['candidats'] -= (int) $movedCisco['candidats'];
            $groups[$heavyIndex]['items'][$bestMove['item_index']]['salles'] -= (int) $movedCisco['salles'];
            $groups[$heavyIndex]['candidats'] -= (int) $movedCisco['candidats'];
            $groups[$heavyIndex]['salles'] -= (int) $movedCisco['salles'];

            $groups[$lightIndex]['items'][] = [
                'type' => 'cisco',
                'label' => $movedCisco['dren'].' / '.$movedCisco['cisco'],
                'dren' => $movedCisco['dren'],
                'cisco' => $movedCisco['cisco'],
                'candidats' => (int) $movedCisco['candidats'],
                'salles' => (int) $movedCisco['salles'],
            ];
            $groups[$lightIndex]['candidats'] += (int) $movedCisco['candidats'];
            $groups[$lightIndex]['salles'] += (int) $movedCisco['salles'];

            $adjustments[] = [
                'item' => $movedCisco['dren'].' / '.$movedCisco['cisco'],
                'from_group' => $groups[$heavyIndex]['group'],
                'to_group' => $groups[$lightIndex]['group'],
                'candidats' => (int) $movedCisco['candidats'],
                'salles' => (int) $movedCisco['salles'],
            ];
        }

        return $this->finalizeGroupScenario($groups, $metric, $adjustments);
    }

    private function makeEmptyGroups(int $groupCount): array
    {
        $groups = [];

        for ($i = 1; $i <= $groupCount; $i++) {
            $groups[] = [
                'group' => $i,
                'items' => [],
                'drens' => [],
                'candidats' => 0,
                'salles' => 0,
            ];
        }

        return $groups;
    }

    private function findLightestGroupIndex(array $groups, string $metric): int
    {
        $targetIndex = 0;

        foreach ($groups as $index => $group) {
            if ($group[$metric] < $groups[$targetIndex][$metric]) {
                $targetIndex = $index;
            }
        }

        return $targetIndex;
    }

    private function finalizeGroupScenario(array $groups, string $metric, array $adjustments = []): array
    {
        usort($groups, fn (array $a, array $b) => $a['group'] <=> $b['group']);

        foreach ($groups as &$group) {
            usort($group['items'], fn (array $a, array $b) => ($b[$metric] <=> $a[$metric]) ?: strcmp((string) $a['label'], (string) $b['label']));
            $group['dren_labels'] = collect($group['items'])
                ->map(fn (array $item) => (string) $item['label'])
                ->values()
                ->all();
        }
        unset($group);

        $totals = array_map(fn (array $group) => (int) $group[$metric], $groups);
        $target = count($groups) > 0 ? (int) round(array_sum($totals) / count($groups)) : 0;

        return [
            'metric' => $metric,
            'target' => $target,
            'spread' => $totals === [] ? 0 : max($totals) - min($totals),
            'groups' => $groups,
            'adjustments' => $adjustments,
        ];
    }

    private function getFilteredRows(Request $request): array
    {
        $annees = DB::table('repartition_salles')
            ->select('annee')
            ->distinct()
            ->orderByDesc('annee')
            ->pluck('annee')
            ->toArray();
        $drens = DB::table('drens')
            ->select('nom')
            ->orderBy('nom')
            ->pluck('nom')
            ->toArray();
        $defaultMarginPercent = (float) (GlobalSetting::query()->value('bepc_copy_margin_percent') ?? 5);
        $marginPercent = (float) $request->query('margin_percent', $defaultMarginPercent);
        $marginPercent = max(0, min(100, $marginPercent));
        $marginRatio = 1 + ($marginPercent / 100);

        $filters = [
            'annee' => (string) $request->query('annee', ''),
            'type_examen' => strtoupper((string) $request->query('type_examen', self::TYPE_ALL)),
            'dren' => (string) $request->query('dren', ''),
            'cisco' => (string) $request->query('cisco', ''),
            'centre_search' => trim((string) $request->query('centre_search', '')),
        ];

        if (! in_array($filters['type_examen'], [self::TYPE_ALL, self::TYPE_BEPC, self::TYPE_CEPE], true)) {
            $filters['type_examen'] = self::TYPE_ALL;
        }

        $ciscosQuery = DB::table('ciscos as cs')
            ->join('drens as d', 'd.id', '=', 'cs.dren_id')
            ->select('cs.nom')
            ->orderBy('cs.nom');

        if ($filters['dren'] !== '') {
            $ciscosQuery->where('d.nom', $filters['dren']);
        }

        $ciscos = $ciscosQuery
            ->pluck('cs.nom')
            ->toArray();

        $ciscosByDren = DB::table('ciscos as cs')
            ->join('drens as d', 'd.id', '=', 'cs.dren_id')
            ->orderBy('d.nom')
            ->orderBy('cs.nom')
            ->get(['d.nom as dren', 'cs.nom as cisco'])
            ->groupBy('dren')
            ->map(fn (Collection $items) => $items->pluck('cisco')->values()->all())
            ->toArray();

        $query = DB::table('repartition_salles as rs')
            ->join('centre_ecrits as ce', 'ce.id', '=', 'rs.centre_ecrit_id')
            ->join('centre_corrections as cc', 'cc.id', '=', 'ce.centre_correction_id')
            ->join('ciscos as cs', 'cs.id', '=', 'cc.cisco_id')
            ->join('drens as d', 'd.id', '=', 'cs.dren_id')
            ->select([
                'rs.annee',
                'rs.langue',
                'rs.numero_salle',
                'rs.effectif',
                'rs.axe_dispatching',
                'rs.point_largage',
                'ce.id as centre_ecrit_id',
                'ce.nom as centre_ecrit',
                'cc.nom as centre_correction',
                'cs.nom as cisco',
                'd.nom as dren',
            ])
            ->orderBy('d.nom')
            ->orderBy('cs.nom')
            ->orderBy('cc.nom')
            ->orderBy('ce.nom')
            ->orderBy('rs.numero_salle');

        if ($filters['annee'] !== '') {
            $query->where('rs.annee', $filters['annee']);
        }
        if ($filters['dren'] !== '') {
            $query->where('d.nom', $filters['dren']);
        }
        if ($filters['cisco'] !== '') {
            $query->where('cs.nom', $filters['cisco']);
        }
        if ($filters['centre_search'] !== '') {
            $query->where('ce.nom', 'like', '%'.$filters['centre_search'].'%');
        }

        if ($filters['type_examen'] === self::TYPE_BEPC) {
            $query->where('rs.langue', '!=', self::CEPE_KEY);
        } elseif ($filters['type_examen'] === self::TYPE_CEPE) {
            $query->where('rs.langue', self::CEPE_KEY);
        }

        $rows = $query->get()->map(function ($row) {
            $row->type_examen = $row->langue === self::CEPE_KEY ? self::TYPE_CEPE : self::TYPE_BEPC;

            return $row;
        });

        $rows = $this->filterOutEmptySalles($rows);

        return [$rows, $filters, $annees, $drens, $ciscos, $ciscosByDren];
    }

    private function getCepeRowsForLivraison(Request $request): array
    {
        $annees = DB::table('repartition_salles')
            ->select('annee')
            ->distinct()
            ->orderByDesc('annee')
            ->pluck('annee')
            ->toArray();
        $drens = DB::table('drens')
            ->select('nom')
            ->orderBy('nom')
            ->pluck('nom')
            ->toArray();

        $filters = [
            'annee' => (string) $request->query('annee', ''),
            'dren' => (string) $request->query('dren', ''),
            'type_examen' => self::TYPE_CEPE,
        ];

        $query = DB::table('repartition_salles as rs')
            ->join('centre_ecrits as ce', 'ce.id', '=', 'rs.centre_ecrit_id')
            ->join('centre_corrections as cc', 'cc.id', '=', 'ce.centre_correction_id')
            ->join('ciscos as cs', 'cs.id', '=', 'cc.cisco_id')
            ->join('drens as d', 'd.id', '=', 'cs.dren_id')
            ->select([
                'rs.annee',
                'rs.langue',
                'rs.numero_salle',
                'rs.effectif',
                'rs.axe_dispatching',
                'rs.point_largage',
                'ce.id as centre_ecrit_id',
                'ce.nom as centre_ecrit',
                'cc.nom as centre_correction',
                'cs.nom as cisco',
                'd.nom as dren',
            ])
            ->where('rs.langue', self::CEPE_KEY)
            ->orderBy('d.nom')
            ->orderBy('cs.nom')
            ->orderBy('cc.nom')
            ->orderBy('ce.nom')
            ->orderBy('rs.numero_salle');

        if ($filters['annee'] !== '') {
            $query->where('rs.annee', $filters['annee']);
        }
        if ($filters['dren'] !== '') {
            $query->where('d.nom', $filters['dren']);
        }

        $rows = $query->get()->map(function ($row) {
            $row->type_examen = self::TYPE_CEPE;

            return $row;
        });

        $rows = $this->filterOutEmptySalles($rows);

        return [$rows, $filters, $annees, $drens];
    }

    private function normalizeBepcLangueBucket(string $langue): string
    {
        if ($this->isForeignOptionLabel($langue, 'A')) {
            return 'Etranger Option A';
        }

        if ($this->isForeignOptionLabel($langue, 'B')) {
            return 'Etranger Option B';
        }

        return $langue;
    }

    private function isForeignOptionLabel(string $langue, string $option): bool
    {
        $normalized = mb_strtolower(trim($langue));
        $normalized = str_replace('étranger', 'etranger', $normalized);
        $option = strtoupper($option);

        return str_starts_with($normalized, mb_strtolower("Etranger Option {$option}"));
    }

    private function sumForeignOption(Collection $rows, string $option): int
    {
        return (int) $rows
            ->filter(fn ($row) => $this->isForeignOptionLabel((string) $row->langue, $option))
            ->sum('effectif');
    }

    private function buildBookData(Collection $rows, array $options = []): array
    {
        $langueOrder = array_flip(RepartitionSalle::LANGUES);
        $bepcLanguesOnly = (bool) ($options['bepc_langues_only'] ?? false);

        return $rows
            ->groupBy(fn ($row) => $row->centre_ecrit_id.'|'.$row->annee.'|'.$row->type_examen)
            ->map(function (Collection $centreRows) use ($langueOrder, $bepcLanguesOnly) {
                $first = $centreRows->first();
                $typeExamen = (string) $first->type_examen;
                $isBepc = $typeExamen === self::TYPE_BEPC;
                $bepcLangueMap = [
                    'Anglais' => 'Ang',
                    'Allemand' => 'ALL',
                    'Esp' => 'ESP',
                    'Option B' => 'B',
                    'Etranger Option A' => 'ETR A',
                    'Etranger Option B' => 'ETR B',
                ];
                $bepcLangueOrder = $bepcLanguesOnly && $isBepc
                    ? ['Anglais', 'Allemand', 'Esp']
                    : ['Anglais', 'Allemand', 'Esp', 'Option B', 'Etranger Option A', 'Etranger Option B'];

                $displayRows = $bepcLanguesOnly && $isBepc
                    ? $centreRows->filter(fn ($row) => in_array((string) $row->langue, ['Anglais', 'Allemand', 'Esp'], true))
                    : $centreRows;

                if ($displayRows->isEmpty()) {
                    return null;
                }

                $salles = $displayRows->pluck('numero_salle')->unique()->sort()->values();
                $salleChunks = $salles->chunk(self::MAX_SALLES_PER_TABLE);
                $rowsByLangue = $displayRows->groupBy(fn ($row) => $isBepc
                    ? $this->normalizeBepcLangueBucket((string) $row->langue)
                    : (string) $row->langue);

                if ($isBepc) {
                    $labels = collect($bepcLangueOrder)
                        ->filter(fn (string $langue) => (int) ($rowsByLangue->get($langue, collect())->sum('effectif')) > 0)
                        ->map(fn (string $langue) => $bepcLangueMap[$langue])
                        ->values();
                } else {
                    $labels = $rowsByLangue
                        ->filter(fn (Collection $langueRows) => (int) $langueRows->sum('effectif') > 0)
                        ->keys()
                        ->map(fn (string $langue) => $langue === self::CEPE_KEY ? 'Total CEPE' : $langue)
                        ->sortBy(function (string $label) use ($langueOrder) {
                            if ($label === 'Total CEPE') {
                                return -1;
                            }

                            return $langueOrder[$label] ?? 999;
                        })
                        ->values();
                }
                if ($labels->isEmpty()) {
                    $labels = $rowsByLangue->keys()
                        ->map(fn (string $langue) => $langue === self::CEPE_KEY ? 'Total CEPE' : ($bepcLangueMap[$langue] ?? $langue))
                        ->take(1)
                        ->values();
                }

                $tables = $salleChunks->map(function (Collection $chunk, int $index) use ($labels, $rowsByLangue) {
                    $tableRows = $labels->map(function (string $label) use ($chunk, $rowsByLangue) {
                        $langueKey = match ($label) {
                            'Ang' => 'Anglais',
                            'ALL' => 'Allemand',
                            'ESP' => 'Esp',
                            'B' => 'Option B',
                            'ETR A' => 'Etranger Option A',
                            'ETR B' => 'Etranger Option B',
                            'Total CEPE' => self::CEPE_KEY,
                            default => $label,
                        };
                        $langueRowsBySalle = $rowsByLangue
                            ->get($langueKey, collect())
                            ->groupBy('numero_salle')
                            ->map(fn (Collection $rows) => (int) $rows->sum('effectif'));

                        return [
                            'label' => $label,
                            'values' => $chunk->mapWithKeys(function (int $salle) use ($langueRowsBySalle) {
                                return [$salle => (int) ($langueRowsBySalle->get($salle, 0))];
                            }),
                        ];
                    });

                    $totauxSalles = $chunk->mapWithKeys(function (int $salle) use ($tableRows) {
                        return [$salle => (int) $tableRows->sum(fn (array $row) => (int) ($row['values'][$salle] ?? 0))];
                    });

                    return [
                        'index' => $index + 1,
                        'salles' => $chunk->values(),
                        'rows' => $tableRows,
                        'totaux_salles' => $totauxSalles,
                    ];
                })->values();

                $pe = $this->countDistinctSalles($displayRows);
                $geDistribution = $this->getGeDistribution($pe, (string) $first->type_examen);
                $geDistributionProbleme = $first->type_examen === self::TYPE_CEPE
                    ? $this->getGeDistribution($pe, self::TYPE_BEPC)
                    : [];
                $geDistributionAutres = $first->type_examen === self::TYPE_CEPE
                    ? $this->getGeDistribution($pe, self::TYPE_CEPE)
                    : [];
                $langueTotals = [];
                $langueSalleCounts = [];
                foreach ($bepcLangueOrder as $langue) {
                    $langueRows = $rowsByLangue->get($langue, collect());
                    $total = (int) $langueRows->sum('effectif');
                    if ($total <= 0) {
                        continue;
                    }
                    $langueTotals[$langue] = $total;
                    $langueSalleCounts[$langue] = $langueRows
                        ->filter(fn ($row) => (int) $row->effectif > 0)
                        ->pluck('numero_salle')
                        ->unique()
                        ->count();
                }

                return [
                    'dren' => $first->dren,
                    'cisco' => $first->cisco,
                    'centre_correction' => $first->centre_correction,
                    'centre_ecrit' => $first->centre_ecrit,
                    'axe_dispatching' => trim((string) ($first->axe_dispatching ?? '')) ?: ($first->dren.' > '.$first->cisco.' > '.$first->centre_correction.' > '.$first->centre_ecrit),
                    'point_largage' => trim((string) ($first->point_largage ?? '')),
                    'annee' => $first->annee,
                    'type_examen' => $first->type_examen,
                    'total_candidats' => $displayRows->sum('effectif'),
                    'total_salles' => $pe,
                    'pe' => $pe,
                    'ge_count' => count($geDistribution),
                    'ge_distribution' => $geDistribution,
                    'ge_distribution_probleme' => $geDistributionProbleme,
                    'ge_distribution_autres' => $geDistributionAutres,
                    'langue_totals' => $langueTotals,
                    'langue_salles' => $langueSalleCounts,
                    'tables' => $tables,
                ];
            })
            ->filter()
            ->values()
            ->toArray();
    }

    private function buildBooksByDren(Collection $rows, array $bookData): array
    {
        $centresByDren = collect($bookData)->groupBy('dren');

        return $rows
            ->groupBy('dren')
            ->map(function (Collection $drenRows, string $drenName) use ($centresByDren) {
                $centres = ($centresByDren->get($drenName, collect()))->values()->all();

                return [
                    'dren' => $drenName,
                    'pages' => $this->paginateCentresForPdf($centres),
                ];
            })
            ->values()
            ->toArray();
    }

    private function buildRecapSheets(array $bookData): array
    {
        return collect($bookData)
            ->groupBy('dren')
            ->map(function (Collection $centres, string $dren) {
                $rows = $centres->map(function (array $centre) {
                    return [
                        'cisco' => $centre['cisco'],
                        'centre_correction' => $centre['centre_correction'],
                        'centre_ecrit' => $centre['centre_ecrit'],
                        'type_examen' => $centre['type_examen'],
                        'annee' => $centre['annee'],
                        'candidats' => (int) $centre['total_candidats'],
                        'salles' => (int) $centre['total_salles'],
                        'pe' => (int) $centre['total_salles'],
                        'ge_total' => (int) $centre['ge_count'],
                        'ge_repartition' => implode('+', array_map(fn (int $n) => (string) $n, $centre['ge_distribution'] ?? [])),
                    ];
                })->values();

                $totalCorrection = $centres->pluck('centre_correction')->unique()->count();
                $totalEcrit = $centres->pluck('centre_ecrit')->unique()->count();

                return [
                    'dren' => $dren,
                    'rows' => $rows->all(),
                    'total_centres' => $rows->count(),
                    'total_correction' => $totalCorrection,
                    'total_ecrit' => $totalEcrit,
                    'total_candidats' => (int) $rows->sum('candidats'),
                    'total_salles' => (int) $rows->sum('salles'),
                    'total_ge' => (int) $rows->sum('ge_total'),
                ];
            })
            ->values()
            ->toArray();
    }

    private function paginateCentresByDren(array $centres): array
    {
        return collect($centres)
            ->groupBy('dren')
            ->map(function (Collection $drenCentres, string $dren) {
                $pages = $drenCentres
                    ->groupBy('cisco')
                    ->map(fn (Collection $items) => $items->values()->all())
                    ->values()
                    ->all();

                return [
                    'dren' => $dren,
                    'pages' => $pages,
                ];
            })
            ->values()
            ->toArray();
    }

    private function getLivreCentreWeight(array $centre, int $pageCapacity = 4): int
    {
        $pe = (int) ($centre['pe'] ?? 0);

        if ($pe > self::MAX_SALLES_PER_TABLE) {
            return $pageCapacity;
        }

        if ($pe > 12) {
            return 2;
        }

        return 1;
    }

    private function paginateCentresForPdf(array $centres): array
    {
        return array_chunk($centres, self::CENTRES_PER_PAGE);
    }

    private function paginateCollection(Collection $items, int $perPage, string $pageName, Request $request): LengthAwarePaginator
    {
        $perPage = min(max($perPage, 1), 50);
        $currentPage = LengthAwarePaginator::resolveCurrentPage($pageName);
        $currentItems = $items->forPage($currentPage, $perPage)->values();

        $paginator = new LengthAwarePaginator(
            $currentItems,
            $items->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'pageName' => $pageName,
            ]
        );

        $paginator->appends($request->query());

        return $paginator;
    }

    private function getGeDistribution(int $pe, string $typeExamen = self::TYPE_BEPC): array
    {
        if ($pe <= 0) {
            return [];
        }

        $pePerGe = $typeExamen === self::TYPE_CEPE ? self::GE_PE_CEPE : self::GE_PE_BEPC;

        if ($typeExamen === self::TYPE_CEPE) {
            $groupCount = max(1, (int) ceil($pe / $pePerGe));
            $base = intdiv($pe, $groupCount);
            $remainder = $pe % $groupCount;

            $distribution = array_fill(0, $groupCount, $base);
            for ($i = 0; $i < $remainder; $i++) {
                $distribution[$i]++;
            }

            return $distribution;
        }

        $distribution = array_fill(0, intdiv($pe, $pePerGe), $pePerGe);
        $reste = $pe % $pePerGe;

        if ($reste === 2) {
            $distribution[] = 2;
        } elseif ($reste === 1) {
            if (count($distribution) > 0) {
                $distribution[count($distribution) - 1] = 2;
                $distribution[] = 2;
            } else {
                $distribution[] = 1;
            }
        }

        return $distribution;
    }

    private function getGlobalSettings(): GlobalSetting
    {
        $setting = GlobalSetting::query()->first();

        if ($setting) {
            return $setting;
        }

        return GlobalSetting::query()->create([
            'bepc_copy_margin_percent' => 5,
            'subject_soubique_ge_capacity' => 6,
            'subject_soubique_subject_capacity' => 9,
            'sord_sheet_page_capacity' => 16,
            'cepe_pages_francais' => 1,
            'cepe_pages_connaissances_usuelles' => 1,
            'cepe_pages_geographie' => 1,
            'cepe_pages_malagasy' => 1,
            'cepe_pages_operation' => 1,
            'cepe_pages_probleme' => 1,
            'cepe_pages_tffmom' => 1,
            'bepc_pages_malagasy' => 1,
            'bepc_pages_svt' => 1,
            'bepc_pages_francais' => 1,
            'bepc_pages_anglais' => 1,
            'bepc_pages_esp' => 1,
            'bepc_pages_pc' => 1,
            'bepc_pages_math' => 1,
            'bepc_pages_hg' => 1,
            'bepc_pages_all' => 1,
            'bepc_print_order' => null,
            'cepe_print_order' => null,
        ]);
    }

    private function getConfiguredPrintOrder(string $typeExamen, GlobalSetting $settings): array
    {
        $configured = $typeExamen === self::TYPE_CEPE
            ? (string) ($settings->cepe_print_order ?? '')
            : (string) ($settings->bepc_print_order ?? '');

        $defaultOrder = collect($this->getConfiguredSubjects($typeExamen, $settings))
            ->pluck('key')
            ->values()
            ->all();

        if (trim($configured) === '') {
            return $defaultOrder;
        }

        $allowed = array_flip($defaultOrder);
        $parsed = collect(preg_split('/[\s,;\n\r]+/', $configured) ?: [])
            ->map(fn (string $value) => trim(mb_strtolower($value)))
            ->filter(fn (string $value) => $value !== '' && isset($allowed[$value]))
            ->unique()
            ->values()
            ->all();

        return array_values(array_unique([...$parsed, ...$defaultOrder]));
    }

    private function getSubjectOrderIndex(string $typeExamen, string $subjectKey, GlobalSetting $settings): int
    {
        $order = $this->getConfiguredPrintOrder($typeExamen, $settings);
        $index = array_search($subjectKey, $order, true);

        return $index === false ? 999 : ((int) $index + 1);
    }

    private function getConfiguredSubjects(string $typeExamen, GlobalSetting $settings): array
    {
        if ($typeExamen === self::TYPE_CEPE) {
            return [
                ['key' => 'francais', 'label' => 'Français', 'pages' => (int) $settings->cepe_pages_francais, 'subject_type' => self::TYPE_CEPE, 'grouping_type' => self::TYPE_CEPE, 'mode' => 'mandatory'],
                ['key' => 'connaissances_usuelles', 'label' => 'Connaissances usuelles', 'pages' => (int) $settings->cepe_pages_connaissances_usuelles, 'subject_type' => self::TYPE_CEPE, 'grouping_type' => self::TYPE_CEPE, 'mode' => 'mandatory'],
                ['key' => 'geographie', 'label' => 'Géographie', 'pages' => (int) $settings->cepe_pages_geographie, 'subject_type' => self::TYPE_CEPE, 'grouping_type' => self::TYPE_CEPE, 'mode' => 'mandatory'],
                ['key' => 'malagasy', 'label' => 'Malagasy', 'pages' => (int) $settings->cepe_pages_malagasy, 'subject_type' => self::TYPE_CEPE, 'grouping_type' => self::TYPE_CEPE, 'mode' => 'mandatory'],
                ['key' => 'operation', 'label' => 'Opération', 'pages' => (int) $settings->cepe_pages_operation, 'subject_type' => self::TYPE_CEPE, 'grouping_type' => self::TYPE_CEPE, 'mode' => 'mandatory'],
                ['key' => 'probleme', 'label' => 'Problème', 'pages' => (int) $settings->cepe_pages_probleme, 'subject_type' => self::TYPE_CEPE, 'grouping_type' => self::TYPE_BEPC, 'mode' => 'mandatory'],
                ['key' => 'tffmom', 'label' => 'TFFMOM', 'pages' => (int) $settings->cepe_pages_tffmom, 'subject_type' => self::TYPE_CEPE, 'grouping_type' => self::TYPE_CEPE, 'mode' => 'mandatory'],
            ];
        }

        return [
            ['key' => 'malagasy', 'label' => 'Malagasy', 'pages' => (int) $settings->bepc_pages_malagasy, 'subject_type' => self::TYPE_BEPC, 'grouping_type' => self::TYPE_BEPC, 'mode' => 'mandatory'],
            ['key' => 'svt', 'label' => 'SVT', 'pages' => (int) $settings->bepc_pages_svt, 'subject_type' => self::TYPE_BEPC, 'grouping_type' => self::TYPE_BEPC, 'mode' => 'mandatory'],
            ['key' => 'francais', 'label' => 'Français', 'pages' => (int) $settings->bepc_pages_francais, 'subject_type' => self::TYPE_BEPC, 'grouping_type' => self::TYPE_BEPC, 'mode' => 'mandatory'],
            ['key' => 'anglais', 'label' => 'Anglais', 'pages' => (int) $settings->bepc_pages_anglais, 'subject_type' => self::TYPE_BEPC, 'grouping_type' => self::TYPE_BEPC, 'mode' => 'langue', 'langue' => 'Anglais'],
            ['key' => 'esp', 'label' => 'Esp', 'pages' => (int) $settings->bepc_pages_esp, 'subject_type' => self::TYPE_BEPC, 'grouping_type' => self::TYPE_BEPC, 'mode' => 'langue', 'langue' => 'Esp'],
            ['key' => 'pc', 'label' => 'PC', 'pages' => (int) $settings->bepc_pages_pc, 'subject_type' => self::TYPE_BEPC, 'grouping_type' => self::TYPE_BEPC, 'mode' => 'mandatory'],
            ['key' => 'math', 'label' => 'Math', 'pages' => (int) $settings->bepc_pages_math, 'subject_type' => self::TYPE_BEPC, 'grouping_type' => self::TYPE_BEPC, 'mode' => 'mandatory'],
            ['key' => 'hg', 'label' => 'HG', 'pages' => (int) $settings->bepc_pages_hg, 'subject_type' => self::TYPE_BEPC, 'grouping_type' => self::TYPE_BEPC, 'mode' => 'mandatory'],
            ['key' => 'all', 'label' => 'ALL', 'pages' => (int) $settings->bepc_pages_all, 'subject_type' => self::TYPE_BEPC, 'grouping_type' => self::TYPE_BEPC, 'mode' => 'langue', 'langue' => 'Allemand'],
        ];
    }

    private function buildSubjectSummaryForCentre(Collection $group, string $typeExamen, GlobalSetting $settings): array
    {
        $first = $group->first();
        $totalSalles = $this->countDistinctSalles($group);
        $subjectDefinitions = $this->getConfiguredSubjects($typeExamen, $settings);
        $subjects = [];

        foreach ($subjectDefinitions as $subject) {
            $pages = max(0, (int) ($subject['pages'] ?? 0));
            if ($pages === 0) {
                continue;
            }

            if (($subject['mode'] ?? 'mandatory') === 'langue') {
                $langue = (string) ($subject['langue'] ?? '');
                $candidates = (int) $group->where('langue', $langue)->sum('effectif');
                $roomCount = $group
                    ->filter(fn ($row) => (string) $row->langue === $langue && (int) $row->effectif > 0)
                    ->pluck('numero_salle')
                    ->unique()
                    ->count();
            } else {
                $candidates = (int) $group->sum('effectif');
                $roomCount = $totalSalles;
            }

            if ($candidates <= 0 || $roomCount <= 0) {
                continue;
            }

            $geCount = count($this->getGeDistribution($roomCount, (string) ($subject['grouping_type'] ?? $typeExamen)));

            $subjects[] = [
                'key' => (string) $subject['key'],
                'label' => (string) $subject['label'],
                'order_index' => $this->getSubjectOrderIndex($typeExamen, (string) $subject['key'], $settings),
                'pages' => $pages,
                'candidates' => $candidates,
                'room_count' => $roomCount,
                'pe_count' => $roomCount,
                'pe_total_with_margin' => $candidates + ($roomCount * self::SUBJECT_PE_MARGIN),
                'ge_count' => $geCount,
            ];
        }

        return [
            'dren' => (string) $first->dren,
            'cisco' => (string) $first->cisco,
            'centre_correction' => (string) $first->centre_correction,
            'centre_ecrit' => (string) $first->centre_ecrit,
            'annee' => (string) $first->annee,
            'type_examen' => $typeExamen,
            'total_candidats' => (int) $group->sum('effectif'),
            'total_salles' => $totalSalles,
            'total_pe_count' => (int) collect($subjects)->sum('pe_count'),
            'total_pe_with_margin' => (int) collect($subjects)->sum('pe_total_with_margin'),
            'centre_ge_count' => (int) collect($subjects)->max('ge_count'),
            'subjects' => $subjects,
        ];
    }

    private function calculateSubjectSoubiqueCount(array $subjects, GlobalSetting $settings): int
    {
        return count($this->buildSubjectSoubiqueDetails($subjects, self::TYPE_ALL, $settings));
    }

    private function buildSubjectSoubiqueDetails(array $subjects, string $typeExamen, GlobalSetting $settings): array
    {
        if ($subjects === []) {
            return [];
        }

        $geCapacity = max(1, (int) $settings->subject_soubique_ge_capacity);
        $subjectCapacity = max(1, (int) $settings->subject_soubique_subject_capacity);
        $orderedSubjects = collect($subjects)
            ->sortBy(fn (array $subject) => $this->getSubjectOrderIndex($typeExamen, (string) $subject['key'], $settings))
            ->values();
        $segments = [];

        foreach ($orderedSubjects as $subject) {
            $totalGe = max(0, (int) ($subject['ge_count'] ?? 0));
            if ($totalGe <= 0) {
                continue;
            }

            $currentStart = 1;
            while ($currentStart <= $totalGe) {
                $currentEnd = min($currentStart + $geCapacity - 1, $totalGe);
                $segments[] = [
                    'key' => $subject['key'],
                    'label' => $subject['label'],
                    'order_index' => $subject['order_index'] ?? null,
                    'pe_count' => $subject['pe_count'] ?? 0,
                    'pe_total_with_margin' => $subject['pe_total_with_margin'] ?? 0,
                    'ge_range' => $currentStart.'-'.$currentEnd,
                    'ge_count' => $subject['ge_count'],
                    'ge_start' => $currentStart,
                    'ge_end' => $currentEnd,
                    'is_split' => $totalGe > $geCapacity,
                ];
                $currentStart = $currentEnd + 1;
            }
        }

        $details = [];
        foreach (array_chunk($segments, $subjectCapacity) as $index => $chunk) {
            $details[] = [
                'index' => $index + 1,
                'ge_start' => collect($chunk)->min('ge_start') ?? 0,
                'ge_end' => collect($chunk)->max('ge_end') ?? 0,
                'subject_count' => count($chunk),
                'subjects' => array_values($chunk),
                'check_label' => 'Matières regroupées',
            ];
        }

        return $details;
    }

    private function buildSubjectSoubiqueRows(Collection $rows, string $typeExamen, GlobalSetting $settings): Collection
    {
        return $rows
            ->groupBy(fn ($row) => implode('|', [$row->centre_ecrit_id, $row->annee, $row->type_examen]))
            ->map(function (Collection $group) use ($typeExamen, $settings) {
                $first = $group->first();
                $resolvedType = $typeExamen === self::TYPE_ALL ? (string) $first->type_examen : $typeExamen;
                $summary = $this->buildSubjectSummaryForCentre($group, $resolvedType, $settings);
                $activeSubjects = collect($summary['subjects'])->pluck('label')->values();
                $subjectGeTotal = (int) collect($summary['subjects'])->sum('ge_count');
                $summary['active_subjects'] = $activeSubjects->all();
                $summary['active_subject_count'] = $activeSubjects->count();
                $summary['subject_ge_total'] = $subjectGeTotal;
                $summary['centre_ge_count'] = (int) collect($summary['subjects'])->max('ge_count');
                $summary['soubique_details'] = $this->buildSubjectSoubiqueDetails($summary['subjects'], $resolvedType, $settings);
                $summary['soubique_sujets'] = count($summary['soubique_details']);

                return $summary;
            })
            ->sortBy(['dren', 'cisco', 'centre_correction', 'centre_ecrit'])
            ->values();
    }

    private function buildSubjectPrintRows(Collection $rows, string $typeExamen, GlobalSetting $settings, string $printMode, int $marginPerRoom = 5): Collection
    {
        $sheetPageCapacity = max(1, (int) $settings->sord_sheet_page_capacity);
        $marginPerRoom = max(0, $marginPerRoom);

        return $rows
            ->groupBy(fn ($row) => implode('|', [$row->centre_ecrit_id, $row->annee, $row->type_examen]))
            ->map(function (Collection $group) use ($typeExamen, $settings, $printMode, $sheetPageCapacity, $marginPerRoom) {
                $first = $group->first();
                $resolvedType = $typeExamen === self::TYPE_ALL ? (string) $first->type_examen : $typeExamen;
                $summary = $this->buildSubjectSummaryForCentre($group, $resolvedType, $settings);
                $subjects = collect($summary['subjects'])
                    ->map(function (array $subject) use ($printMode, $sheetPageCapacity, $marginPerRoom) {
                        $pages = max(1, (int) $subject['pages']);
                        $candidates = (int) $subject['candidates'];
                        $roomCount = (int) ($subject['room_count'] ?? 0);
                        $segments = [];

                        if ($printMode === 'sord') {
                            $marginSurplus = $roomCount * $marginPerRoom;
                            $exemplaires = $candidates + $marginSurplus;
                            $remainingPages = $pages;
                            while ($remainingPages > 0) {
                                $segmentPages = min(4, $remainingPages);
                                $copiesPerSheet = max(1, (int) floor($sheetPageCapacity / $segmentPages));
                                $segmentSheets = (int) ceil($exemplaires / $copiesPerSheet);
                                $segments[] = [
                                    'pages' => $segmentPages,
                                    'copies_per_sheet' => $copiesPerSheet,
                                    'feuilles' => $segmentSheets,
                                ];
                                $remainingPages -= $segmentPages;
                            }

                            $copiesPerSheet = max(1, (int) ($segments[0]['copies_per_sheet'] ?? 1));
                            $feuilles = (int) collect($segments)->sum('feuilles');
                            $impressions = $feuilles;
                        } else {
                            $copiesPerSheet = 1;
                            $marginSurplus = 0;
                            $exemplaires = $candidates;
                            $feuillesParExemplaire = (int) ceil($pages / 2);
                            $feuilles = (int) ($exemplaires * $feuillesParExemplaire);
                            $impressions = $candidates * $pages;
                            $segments[] = [
                                'pages' => $pages,
                                'copies_per_sheet' => 1,
                                'feuilles' => $feuilles,
                            ];
                        }

                        return [
                            'subject_key' => (string) $subject['key'],
                            'label' => (string) $subject['label'],
                            'pages' => $pages,
                            'candidates' => $candidates,
                            'room_count' => $roomCount,
                            'margin_surplus' => $marginSurplus,
                            'exemplaires' => $exemplaires,
                            'copies_per_sheet' => $copiesPerSheet,
                            'feuilles' => $feuilles,
                            'impressions' => $impressions,
                            'segments' => $segments,
                        ];
                    })
                    ->sortBy('label')
                    ->values();

                $summary['subjects'] = $subjects->all();
                $summary['total_exemplaires'] = (int) $subjects->sum('exemplaires');
                $summary['total_feuilles'] = (int) $subjects->sum('feuilles');
                $summary['total_impressions'] = (int) $subjects->sum('impressions');

                return $summary;
            })
            ->sortBy(['dren', 'cisco', 'centre_correction', 'centre_ecrit'])
            ->values();
    }

    private function filterOutEmptySalles(Collection $rows): Collection
    {
        if ($rows->isEmpty()) {
            return $rows;
        }

        $nonEmptyKeys = $rows
            ->groupBy(fn ($row) => $row->centre_ecrit_id.'|'.$row->annee.'|'.$row->type_examen.'|'.$row->numero_salle)
            ->filter(fn (Collection $group) => (int) $group->sum('effectif') > 0)
            ->keys()
            ->flip();

        return $rows
            ->filter(fn ($row) => $nonEmptyKeys->has($row->centre_ecrit_id.'|'.$row->annee.'|'.$row->type_examen.'|'.$row->numero_salle))
            ->values();
    }

    private function countDistinctSalles(Collection $rows): int
    {
        return $rows
            ->map(fn ($row) => $row->centre_ecrit_id.'|'.$row->annee.'|'.$row->type_examen.'|'.$row->numero_salle)
            ->unique()
            ->count();
    }

    private function styleSheetWithHeader(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): void
    {
        $lastCol = $sheet->getHighestColumn();
        $lastColIndex = Coordinate::columnIndexFromString($lastCol);

        $sheet->getStyle("A1:{$lastCol}1")->getFont()->setBold(true);
        $sheet->getStyle("A1:{$lastCol}1")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');
        $sheet->getStyle("A1:{$lastCol}{$sheet->getHighestRow()}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        for ($i = 1; $i <= $lastColIndex; $i++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }
    }

    private function addCiscoRecapSheet(Spreadsheet $spreadsheet, Collection $rows): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('RECAP_CISCO');
        $headers = [
            'DREN',
            'CISCO',
            'NOMBRE DE CENTRES DE CORRECTION',
            'NOMBRE DE CENTRES ECRIT',
            'TOTAL CANDIDATS',
            'TOTAL SALLES',
            'TOTAL ANGLAIS',
            'TOTAL ALL',
            'TOTAL ESP',
            'TOTAL OPTION B',
        ];
        $sheet->fromArray($headers, null, 'A1');

        $recapRows = $rows
            ->groupBy(fn ($row) => implode('|', [$row->dren, $row->cisco]))
            ->map(function (Collection $group) {
                $sum = fn (string $langue): int => (int) $group->where('langue', $langue)->sum('effectif');

                return [
                    'dren' => (string) $group->first()->dren,
                    'cisco' => (string) $group->first()->cisco,
                    'centres_correction' => $group->pluck('centre_correction')->unique()->count(),
                    'centres_ecrit' => $group->pluck('centre_ecrit_id')->unique()->count(),
                    'total_candidats' => (int) $group->sum('effectif'),
                    'total_salles' => $this->countDistinctSalles($group),
                    'anglais' => $sum('Anglais'),
                    'all' => $sum('Allemand'),
                    'esp' => $sum('Esp'),
                    'option_b' => $sum('Option B'),
                ];
            })
            ->sortBy(['dren', 'cisco'])
            ->values();

        $totals = [
            'centres_correction' => 0,
            'centres_ecrit' => 0,
            'total_candidats' => 0,
            'total_salles' => 0,
            'anglais' => 0,
            'all' => 0,
            'esp' => 0,
            'option_b' => 0,
        ];

        $rowIndex = 2;
        foreach ($recapRows as $line) {
            $sheet->fromArray([
                $line['dren'],
                $line['cisco'],
                $line['centres_correction'],
                $line['centres_ecrit'],
                $line['total_candidats'],
                $line['total_salles'],
                $line['anglais'],
                $line['all'],
                $line['esp'],
                $line['option_b'],
            ], null, "A{$rowIndex}");

            foreach ($totals as $key => $value) {
                $totals[$key] = $value + (int) $line[$key];
            }

            $rowIndex++;
        }

        $sheet->fromArray([
            'TOTAL GLOBAL',
            '',
            $totals['centres_correction'],
            $totals['centres_ecrit'],
            $totals['total_candidats'],
            $totals['total_salles'],
            $totals['anglais'],
            $totals['all'],
            $totals['esp'],
            $totals['option_b'],
        ], null, "A{$rowIndex}");

        $sheet->getStyle('A1:J1')->getFont()->setBold(true);
        $sheet->getStyle("A{$rowIndex}:J{$rowIndex}")->getFont()->setBold(true);
        $sheet->getStyle('A1:J1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');
        $sheet->getStyle("A1:J{$rowIndex}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    private function buildCentreKey(string $typeExamen, string $dren, string $cisco, string $centreCorrection, string $centreEcrit): string
    {
        return implode('|', [
            $this->normalizeCompareKey($typeExamen),
            $this->normalizeCompareKey($dren),
            $this->normalizeCompareKey($cisco),
            $this->normalizeCompareKey($centreCorrection),
            $this->normalizeCompareKey($centreEcrit),
        ]);
    }

    private function buildCentreKeyHash(string $typeExamen, string $dren, string $cisco, string $centreCorrection, string $centreEcrit): string
    {
        return hash('sha256', $this->buildCentreKey($typeExamen, $dren, $cisco, $centreCorrection, $centreEcrit));
    }

    private function normalizeCompareKey(string $value): string
    {
        $value = trim(mb_strtolower($value));
        $value = preg_replace('/\s+/', ' ', $value);
        $normalized = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);

        return $normalized !== false ? $normalized : $value;
    }

    private function parseImportCsvRows(Request $request, string $fileKey): array
    {
        $file = $request->file($fileKey);
        if (! $file) {
            return [];
        }

        $handle = fopen($file->getRealPath(), 'rb');
        if (! $handle) {
            return [];
        }

        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);
            return [];
        }

        $semicolonCols = str_getcsv($firstLine, ';');
        $commaCols = str_getcsv($firstLine, ',');
        $delimiter = count($semicolonCols) > count($commaCols) ? ';' : ',';

        rewind($handle);
        $headers = fgetcsv($handle, 0, $delimiter);
        if (! is_array($headers)) {
            fclose($handle);
            return [];
        }

        $normalizedHeaders = array_map(fn ($header) => $this->normalizeImportHeader((string) $header), $headers);
        $rows = [];

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if ($row === [null] || $row === false) {
                continue;
            }

            $line = [];
            foreach ($normalizedHeaders as $index => $key) {
                if ($key === '') {
                    continue;
                }
                $line[$key] = isset($row[$index])
                    ? $this->normalizeImportValue((string) $row[$index])
                    : null;
            }
            if (count(array_filter($line, fn ($value) => $value !== null && trim((string) $value) !== '')) === 0) {
                continue;
            }
            $rows[] = $line;
        }

        fclose($handle);

        return $rows;
    }

    private function normalizeImportValue(string $value): string
    {
        if ($value === '' || mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        $encoding = mb_detect_encoding($value, ['Windows-1252', 'ISO-8859-1', 'CP850'], true) ?: 'Windows-1252';

        return mb_convert_encoding($value, 'UTF-8', $encoding);
    }

    private function normalizeImportHeader(string $header): string
    {
        $key = trim(mb_strtolower($header));
        $key = preg_replace('/\s+/', '_', $key);
        $key = str_replace(['-', '.'], '_', $key);

        $map = [
            'annee' => 'annee',
            'annee_scolaire' => 'annee',
            'examen' => 'type_examen',
            'type_examen' => 'type_examen',
            'type' => 'type_examen',
            'dren' => 'dren',
            'region' => 'dren',
            'cisco' => 'cisco',
            'district' => 'cisco',
            'ang' => 'anglais',
            'anglais' => 'anglais',
            'esp' => 'espagnol',
            'espagnol' => 'espagnol',
            'espanol' => 'espagnol',
            'all' => 'allemand',
            'allemand' => 'allemand',
            'cc' => 'centre_correction',
            'centre_correction' => 'centre_correction',
            'centre_correction_nom' => 'centre_correction',
            'centre_correction_name' => 'centre_correction',
            'centre_c' => 'centre_correction',
            'ce' => 'centre_ecrit',
            'centre_ecrit' => 'centre_ecrit',
            'centre_ecrit_nom' => 'centre_ecrit',
            'centre_ecrit_name' => 'centre_ecrit',
            'centre_e' => 'centre_ecrit',
            'total_salle' => 'total_salles',
            'total_salles' => 'total_salles',
            'salles' => 'total_salles',
            'nombre_salles' => 'total_salles',
            'total_candidat' => 'total_candidats',
            'total_candidats' => 'total_candidats',
            'candidats' => 'total_candidats',
            'nombre_candidats' => 'total_candidats',
            'option_b' => 'option_b',
            'optionb' => 'option_b',
            'opt_b' => 'option_b',
        ];

        return $map[$key] ?? $key;
    }

    private function buildControleTraceRows(Collection $bookData): array
    {
        $peRows = [];
        $geRows = [];

        foreach ($bookData as $centreIndex => $centre) {
            $distribution = array_values($centre['ge_distribution'] ?? []);
            $currentPe = 1;

            foreach ($distribution as $geOffset => $peCount) {
                $geNo = $geOffset + 1;
                $peStart = $currentPe;
                $peEnd = $currentPe + ((int) $peCount) - 1;
                $rangeLabel = $peStart === $peEnd ? "PE{$peStart}" : "PE{$peStart}-PE{$peEnd}";

                $geRows[] = [
                    'centre_idx' => $centreIndex,
                    'dren' => (string) ($centre['dren'] ?? ''),
                    'cisco' => (string) ($centre['cisco'] ?? ''),
                    'centre_correction' => (string) ($centre['centre_correction'] ?? ''),
                    'centre_ecrit' => (string) ($centre['centre_ecrit'] ?? ''),
                    'type_examen' => (string) ($centre['type_examen'] ?? ''),
                    'annee' => (string) ($centre['annee'] ?? ''),
                    'ge_no' => $geNo,
                    'pe_count' => (int) $peCount,
                    'pe_range' => $rangeLabel,
                ];

                for ($i = 0; $i < (int) $peCount; $i++) {
                    $peRows[] = [
                        'centre_idx' => $centreIndex,
                        'dren' => (string) ($centre['dren'] ?? ''),
                        'cisco' => (string) ($centre['cisco'] ?? ''),
                        'centre_correction' => (string) ($centre['centre_correction'] ?? ''),
                        'centre_ecrit' => (string) ($centre['centre_ecrit'] ?? ''),
                        'type_examen' => (string) ($centre['type_examen'] ?? ''),
                        'annee' => (string) ($centre['annee'] ?? ''),
                        'pe_no' => $currentPe,
                        'ge_no' => $geNo,
                    ];
                    $currentPe++;
                }
            }
        }

        return [
            collect($peRows)->sortBy(['dren', 'cisco', 'centre_correction', 'centre_ecrit', 'type_examen', 'pe_no'])->values(),
            collect($geRows)->sortBy(['dren', 'cisco', 'centre_correction', 'centre_ecrit', 'type_examen', 'ge_no'])->values(),
        ];
    }

    private function buildLivreControlePayload(Request $request): array
    {
        [$rows, $filters, $annees, $drens] = $this->getFilteredRows($request);
        $bookData = collect($this->buildBookData($rows));
        [$peRows, $geRows] = $this->buildControleTraceRows($bookData);
        $traceStore = $this->parseLivreControleTraceStore($request);
        $selectedCompteurMode = $this->resolveControleCompteurMode($traceStore);
        $peRows = $this->hydrateControlePeRows($peRows, $traceStore);
        $geRows = $this->hydrateControleGeRows($geRows, $traceStore);

        return [
            'filters' => $filters,
            'annees' => $annees,
            'drens' => $drens,
            'peRows' => $peRows,
            'geRows' => $geRows,
            'compteurSummaryByDren' => $this->buildControleCompteurSummaryRows($peRows, 'DREN', $traceStore),
            'compteurSummaryByCisco' => $this->buildControleCompteurSummaryRows($peRows, 'CISCO', $traceStore),
            'compteurSummaryByCentre' => $this->buildControleCompteurSummaryRows($peRows, 'CENTRE_ECRIT', $traceStore),
            'selectedCompteurMode' => $selectedCompteurMode,
            'selectedCompteurModeLabel' => $this->controleCompteurModeLabel($selectedCompteurMode),
            'selectedCompteurSummary' => $this->buildControleCompteurSummaryRows(
                $peRows,
                $selectedCompteurMode,
                $traceStore
            ),
            'controleTaskGroups' => $this->buildControleTaskGroups($peRows, $traceStore),
            'traceStore' => $traceStore,
            'stats' => [
                'total_centres' => $bookData->count(),
                'total_pe' => (int) $bookData->sum('pe'),
                'total_ge' => (int) $bookData->sum('ge_count'),
            ],
        ];
    }

    private function buildLivreControleAutoPayload(Request $request): array
    {
        [$rows, $filters, $annees, $drens] = $this->getFilteredRows($request);
        $bookData = collect($this->buildBookData($rows));

        $centreRows = $bookData
            ->map(fn (array $centre) => [
                'dren' => (string) ($centre['dren'] ?? ''),
                'cisco' => (string) ($centre['cisco'] ?? ''),
                'centre_correction' => (string) ($centre['centre_correction'] ?? ''),
                'centre_ecrit' => (string) ($centre['centre_ecrit'] ?? ''),
                'type_examen' => (string) ($centre['type_examen'] ?? ''),
                'annee' => (string) ($centre['annee'] ?? ''),
                'total_candidats' => (int) ($centre['total_candidats'] ?? 0),
                'total_salles' => (int) ($centre['total_salles'] ?? 0),
                'pe' => (int) ($centre['pe'] ?? 0),
                'ge_count' => (int) ($centre['ge_count'] ?? 0),
            ])
            ->sortBy(['dren', 'cisco', 'centre_correction', 'centre_ecrit', 'type_examen'])
            ->values();

        $drenRows = $centreRows
            ->groupBy('dren')
            ->map(fn (Collection $centres, string $dren) => [
                'dren' => $dren,
                'total_candidats' => (int) $centres->sum('total_candidats'),
                'total_salles' => (int) $centres->sum('total_salles'),
                'total_pe' => (int) $centres->sum('pe'),
                'total_ge' => (int) $centres->sum('ge_count'),
                'total_centres' => (int) $centres->count(),
            ])
            ->sortBy('dren')
            ->values();

        return [
            'filters' => $filters,
            'annees' => $annees,
            'drens' => $drens,
            'centreRows' => $centreRows,
            'drenRows' => $drenRows,
            'stats' => [
                'total_drens' => (int) $drenRows->count(),
                'total_centres' => (int) $centreRows->count(),
                'total_candidats' => (int) $centreRows->sum('total_candidats'),
                'total_salles' => (int) $centreRows->sum('total_salles'),
                'total_pe' => (int) $centreRows->sum('pe'),
                'total_ge' => (int) $centreRows->sum('ge_count'),
            ],
        ];
    }

    private function buildLivrePeGePayload(Request $request): array
    {
        [$rows, $filters, $annees, $drens, $ciscos] = $this->getFilteredRows($request);
        $niveau = strtoupper((string) $request->query('niveau', 'DREN'));
        if (! in_array($niveau, ['DREN', 'CISCO', 'CENTRE'], true)) {
            $niveau = 'DREN';
        }

        $centreRows = $this->buildPeGeAdjustedCentreRows($rows);
        $summaryRows = $this->summarizePeGeAdjustedRows($centreRows, $niveau)
            ->filter(fn (array $row) => (int) $row['total_salles_divisees'] > 0)
            ->values();
        $splitCentreRows = $centreRows
            ->filter(fn (array $row) => (int) $row['salles_divisees'] > 0)
            ->values();

        return [
            'filters' => $filters,
            'niveau' => $niveau,
            'annees' => $annees,
            'drens' => $drens,
            'ciscos' => $ciscos,
            'centreRows' => $centreRows,
            'summaryRows' => $summaryRows,
            'splitCentreRows' => $splitCentreRows,
            'stats' => [
                'total_candidats' => (int) $centreRows->sum('total_candidats'),
                'total_salles' => (int) $centreRows->sum('total_salles'),
                'total_salles_divisees' => (int) $centreRows->sum('salles_divisees'),
                'total_pe_normal' => (int) $centreRows->sum('pe_normal'),
                'total_ge_normal' => (int) $centreRows->sum('ge_normal'),
                'total_pe' => (int) $centreRows->sum('pe'),
                'total_ge' => (int) $centreRows->sum('ge_count'),
                'total_centres' => (int) $centreRows->count(),
                'total_centres_divises' => (int) $splitCentreRows->count(),
            ],
        ];
    }

    private function buildPeGeAdjustedCentreRows(Collection $rows): Collection
    {
        return $rows
            ->groupBy(fn ($row) => implode('|', [$row->centre_ecrit_id, $row->annee, $row->type_examen]))
            ->map(function (Collection $centreRows) {
                $first = $centreRows->first();
                $salles = $centreRows
                    ->groupBy('numero_salle')
                    ->map(function (Collection $salleRows, $numeroSalle) {
                        $candidats = (int) $salleRows->sum('effectif');

                        return [
                            'numero_salle' => (int) $numeroSalle,
                            'candidats' => $candidats,
                            'pe' => $candidats >= 50 ? 2 : 1,
                            'is_split' => $candidats >= 50,
                        ];
                    })
                    ->sortBy('numero_salle')
                    ->values();

                $peNormal = (int) $salles->count();
                $geNormalDistribution = $this->getGeDistribution($peNormal, (string) $first->type_examen);
                $pe = (int) $salles->sum('pe');
                $geDistribution = $this->getGeDistribution($pe, (string) $first->type_examen);
                $splitRooms = $salles->filter(fn (array $salle) => (bool) $salle['is_split'])->values();

                return [
                    'dren' => (string) $first->dren,
                    'cisco' => (string) $first->cisco,
                    'centre_correction' => (string) $first->centre_correction,
                    'centre_ecrit' => (string) $first->centre_ecrit,
                    'type_examen' => (string) $first->type_examen,
                    'annee' => (string) $first->annee,
                    'total_candidats' => (int) $centreRows->sum('effectif'),
                    'total_salles' => (int) $salles->count(),
                    'salles_divisees' => (int) $splitRooms->count(),
                    'salles_divisees_percent' => $salles->count() > 0
                        ? round(($splitRooms->count() / $salles->count()) * 100, 1)
                        : 0.0,
                    'pe_normal' => $peNormal,
                    'ge_normal' => (int) count($geNormalDistribution),
                    'pe' => $pe,
                    'ge_count' => (int) count($geDistribution),
                    'ecart_pe' => $pe - $peNormal,
                    'ecart_ge' => (int) count($geDistribution) - (int) count($geNormalDistribution),
                    'ge_distribution' => $geDistribution,
                    'salles_divisees_labels' => $splitRooms
                        ->map(fn (array $salle) => 'S'.$salle['numero_salle'].' ('.$salle['candidats'].' cand.)')
                        ->all(),
                ];
            })
            ->sortBy(['dren', 'cisco', 'centre_correction', 'centre_ecrit', 'type_examen'])
            ->values();
    }

    private function summarizePeGeAdjustedRows(Collection $centreRows, string $niveau): Collection
    {
        return $centreRows
            ->groupBy(function (array $row) use ($niveau) {
                return match ($niveau) {
                    'CENTRE' => implode('|', [
                        $row['dren'],
                        $row['cisco'],
                        $row['centre_correction'],
                        $row['centre_ecrit'],
                        $row['type_examen'],
                    ]),
                    'CISCO' => implode('|', [$row['dren'], $row['cisco']]),
                    default => $row['dren'],
                };
            })
            ->map(function (Collection $group) use ($niveau) {
                $first = $group->first();
                $label = match ($niveau) {
                    'CENTRE' => (string) $first['centre_ecrit'],
                    'CISCO' => (string) $first['cisco'],
                    default => (string) $first['dren'],
                };

                return [
                    'label' => $label,
                    'dren' => (string) $first['dren'],
                    'cisco' => $niveau === 'DREN' ? '' : (string) $first['cisco'],
                    'centre_correction' => $niveau === 'CENTRE' ? (string) $first['centre_correction'] : '',
                    'centre_ecrit' => $niveau === 'CENTRE' ? (string) $first['centre_ecrit'] : '',
                    'type_examen' => $niveau === 'CENTRE' ? (string) $first['type_examen'] : '',
                    'total_centres' => (int) $group->count(),
                    'total_candidats' => (int) $group->sum('total_candidats'),
                    'total_salles' => (int) $group->sum('total_salles'),
                    'total_salles_divisees' => (int) $group->sum('salles_divisees'),
                    'salles_divisees_percent' => (int) $group->sum('total_salles') > 0
                        ? round(((int) $group->sum('salles_divisees') / (int) $group->sum('total_salles')) * 100, 1)
                        : 0.0,
                    'total_pe_normal' => (int) $group->sum('pe_normal'),
                    'total_ge_normal' => (int) $group->sum('ge_normal'),
                    'total_pe' => (int) $group->sum('pe'),
                    'total_ge' => (int) $group->sum('ge_count'),
                    'ecart_pe' => (int) $group->sum('ecart_pe'),
                    'ecart_ge' => (int) $group->sum('ecart_ge'),
                ];
            })
            ->sortBy(['dren', 'cisco', 'centre_ecrit', 'type_examen'])
            ->values();
    }

    private function parseLivreControleTraceStore(Request $request): array
    {
        $payload = $request->input('trace_payload');
        if (! is_string($payload) || trim($payload) === '') {
            return [];
        }

        $decoded = json_decode($payload, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function hydrateControlePeRows(Collection $peRows, array $traceStore): Collection
    {
        return $peRows
            ->map(function (array $row) use ($traceStore) {
                $baseKey = implode('|', ['PE', $row['centre_idx'], $row['type_examen'], $row['pe_no']]);

                $row['compteur'] = (string) ($traceStore[$baseKey.':compteur'] ?? '');
                $row['matiere'] = (string) ($traceStore[$baseKey.':matiere'] ?? '');
                $row['datetime'] = (string) ($traceStore[$baseKey.':datetime'] ?? '');
                $row['agent'] = (string) ($traceStore[$baseKey.':agent'] ?? '');
                $row['obs'] = (string) ($traceStore[$baseKey.':obs'] ?? '');

                return $row;
            })
            ->values();
    }

    private function hydrateControleGeRows(Collection $geRows, array $traceStore): Collection
    {
        return $geRows
            ->map(function (array $row) use ($traceStore) {
                $baseKey = implode('|', ['GE', $row['centre_idx'], $row['type_examen'], $row['ge_no']]);

                $row['compteur'] = (string) ($traceStore[$baseKey.':compteur'] ?? '');
                $row['datetime'] = (string) ($traceStore[$baseKey.':datetime'] ?? '');
                $row['agent'] = (string) ($traceStore[$baseKey.':agent'] ?? '');
                $row['obs'] = (string) ($traceStore[$baseKey.':obs'] ?? '');

                return $row;
            })
            ->values();
    }

    private function buildControleCompteurSummaryRows(Collection $peRows, string $mode, array $traceStore = []): Collection
    {
        $namesText = (string) ($traceStore['compteurNames'] ?? '');
        $names = array_filter(array_map('trim', explode("\n", $namesText)));

        // Parse group data
        $groupCount = max(1, (int) ($traceStore['groupCount'] ?? 1));
        $groups = [];
        for ($i = 1; $i <= $groupCount; $i++) {
            $regionsText = (string) ($traceStore["group{$i}|regions"] ?? '');
            $groupNamesText = (string) ($traceStore["group{$i}|names"] ?? '');
            $groups[] = [
                'regions' => array_filter(array_map('trim', explode("\n", $regionsText))),
                'names' => array_filter(array_map('trim', explode("\n", $groupNamesText)))
            ];
        }

        return $peRows
            ->groupBy(function (array $row) use ($mode) {
                if ($mode === 'CENTRE_ECRIT') {
                    return implode(' / ', array_filter([
                        (string) ($row['dren'] ?? ''),
                        (string) ($row['cisco'] ?? ''),
                        (string) ($row['centre_ecrit'] ?? ''),
                    ], fn ($value) => $value !== ''));
                }

                if ($mode === 'CISCO') {
                    return $row['dren'].' / '.$row['cisco'];
                }

                return $row['dren'];
            })
            ->map(function (Collection $rows, string $label) use ($mode, $traceStore, $names, $groups) {
                $totalPe = (int) $rows->count();
                $storeKey = "matrix|{$mode}|{$label}|compteurs";
                $compteurCount = max(0, (int) ($traceStore[$storeKey] ?? 1));
                $assignedGroupId = (int) ($traceStore["assignment|{$mode}|{$label}"] ?? 0);
                $assignedGroupName = $assignedGroupId > 0 ? $this->controleGroupName($traceStore, $assignedGroupId) : '';
                $assignedCounters = $assignedGroupId > 0 ? $this->controleGroupCounters($traceStore, $assignedGroupId) : [];

                // Find appropriate names for this region
                $regionNames = $names; // Default to global names
                if ($assignedCounters !== []) {
                    $regionNames = collect($assignedCounters)->pluck('name')->all();
                    $compteurCount = count($regionNames);
                } elseif ($assignedGroupName !== '') {
                    $regionNames = [$assignedGroupName];
                    $compteurCount = 1;
                }
                foreach ($groups as $group) {
                    foreach ($group['regions'] as $region) {
                        if (str_contains($label, $region) || str_contains($region, $label)) {
                            $regionNames = $group['names'];
                            break 2;
                        }
                    }
                }

                return [
                    'label' => $label,
                    'group_id' => $assignedGroupId,
                    'group_name' => $assignedGroupName,
                    'total_pe' => $totalPe,
                    'compteur_count' => $compteurCount,
                    'pe_par_compteur' => $compteurCount > 0 ? round($totalPe / $compteurCount, 2) : $totalPe,
                    'repartition' => $this->buildControleCompteurRanges($totalPe, $compteurCount, $regionNames),
                ];
            })
            ->sortBy('label')
            ->values();
    }

    private function buildControleTaskGroups(Collection $peRows, array $traceStore): Collection
    {
        $groupCount = max(1, (int) ($traceStore['groupCount'] ?? 1));

        $groups = collect(range(1, $groupCount))
            ->mapWithKeys(fn (int $groupId) => [$groupId => [
                'id' => $groupId,
                'name' => $this->controleGroupName($traceStore, $groupId),
                'counters' => collect($this->controleGroupCounters($traceStore, $groupId)),
                'tasks' => collect(),
            ]]);

        $peRows
            ->groupBy(fn (array $row) => (string) ($row['dren'] ?? ''))
            ->each(function (Collection $drenRows, string $dren) use (&$groups, $traceStore) {
                $groupId = (int) ($traceStore["assignment|DREN|{$dren}"] ?? 1);
                if ($groupId <= 0) {
                    $groupId = 1;
                }

                if (! $groups->has($groupId)) {
                    $groups->put($groupId, [
                        'id' => $groupId,
                        'name' => $this->controleGroupName($traceStore, $groupId),
                        'counters' => collect($this->controleGroupCounters($traceStore, $groupId)),
                        'tasks' => collect(),
                    ]);
                }

                $group = $groups->get($groupId);
                $counterNames = $group['counters']->pluck('name')->all();
                if ($counterNames === []) {
                    $counterNames = [$group['name']];
                }

                $counterTasks = collect($counterNames)
                    ->mapWithKeys(fn (string $name) => [$name => collect()]);

                $drenRows
                    ->groupBy(fn (array $row) => implode('|', [
                        (string) ($row['dren'] ?? ''),
                        (string) ($row['cisco'] ?? ''),
                        (string) ($row['centre_ecrit'] ?? ''),
                    ]))
                    ->sortKeys()
                    ->each(function (Collection $centreRows) use (&$counterTasks, $counterNames) {
                        $first = $centreRows->first();
                        $peNumbers = $centreRows
                            ->pluck('pe_no')
                            ->map(fn ($value) => (int) $value)
                            ->sort()
                            ->values();

                        $counterCount = max(1, count($counterNames));
                        $base = intdiv($peNumbers->count(), $counterCount);
                        $extra = $peNumbers->count() % $counterCount;
                        $offset = 0;

                        foreach ($counterNames as $index => $counterName) {
                            $count = $base + ($extra > 0 ? 1 : 0);
                            if ($extra > 0) {
                                $extra--;
                            }

                            $slice = $peNumbers->slice($offset, $count)->values();
                            $offset += $count;

                            if ($slice->isEmpty()) {
                                continue;
                            }

                            $start = (int) $slice->first();
                            $end = (int) $slice->last();
                            $range = $start === $end ? "PE{$start}" : "PE{$start}-{$end}";
                            $counterTasks[$counterName]->push([
                                'dren' => (string) ($first['dren'] ?? ''),
                                'cisco' => (string) ($first['cisco'] ?? ''),
                                'centre_ecrit' => (string) ($first['centre_ecrit'] ?? ''),
                                'range' => $range,
                                'text' => (string) ($first['centre_ecrit'] ?? '').' '.$range,
                            ]);
                        }
                    });

                $group['counters'] = $group['counters']
                    ->map(function (array $counter) use ($counterTasks) {
                        $counter['tasks'] = $counterTasks->get($counter['name'], collect())->values();

                        return $counter;
                    });

                $group['tasks'] = $group['counters']->flatMap(fn (array $counter) => $counter['tasks'])->values();
                $groups->put($groupId, $group);
            });

        return $groups
            ->map(function (array $group) {
                $group['tasks'] = $group['tasks']->values();
                $group['counters'] = $group['counters']->values();

                return $group;
            })
            ->sortBy('id')
            ->values();
    }

    private function controleGroupName(array $traceStore, int $groupId): string
    {
        $singleName = trim((string) ($traceStore["group{$groupId}|name"] ?? ''));
        if ($singleName !== '') {
            return $singleName;
        }

        $namesText = (string) ($traceStore["group{$groupId}|names"] ?? '');
        $names = array_values(array_filter(array_map('trim', explode("\n", $namesText))));

        return $names[0] ?? "Groupe {$groupId}";
    }

    private function controleGroupCounters(array $traceStore, int $groupId): array
    {
        $namesText = (string) ($traceStore["group{$groupId}|names"] ?? '');
        $names = array_values(array_filter(array_map('trim', explode("\n", $namesText))));

        if ($names === []) {
            $names = [$this->controleGroupName($traceStore, $groupId)];
        }

        return collect($names)
            ->map(fn (string $name) => [
                'name' => $name,
                'tasks' => collect(),
            ])
            ->values()
            ->all();
    }

    private function resolveControleCompteurMode(array $traceStore): string
    {
        $mode = strtoupper((string) ($traceStore['matrix|mode'] ?? 'DREN'));

        return in_array($mode, ['DREN', 'CISCO', 'CENTRE_ECRIT'], true) ? $mode : 'DREN';
    }

    private function controleCompteurModeLabel(string $mode): string
    {
        return match ($mode) {
            'CISCO' => 'Par CISCO',
            'CENTRE_ECRIT' => 'Par centre',
            default => 'Par DREN',
        };
    }

    private function buildControleCompteurRanges(int $totalPe, int $compteurCount, array $names = []): string
    {
        if ($compteurCount <= 0 || $totalPe <= 0) {
            return '-';
        }

        $base = (int) floor($totalPe / $compteurCount);
        $extra = $totalPe % $compteurCount;
        $start = 1;
        $ranges = [];

        for ($index = 1; $index <= $compteurCount; $index++) {
            $count = $base + ($extra > 0 ? 1 : 0);
            if ($extra > 0) {
                $extra--;
            }

            $name = $names[$index - 1] ?? "C{$index}";

            if ($count <= 0) {
                $ranges[] = "{$name}: -";
                continue;
            }

            $end = $start + $count - 1;
            $ranges[] = $start === $end ? "{$name}: PE{$start}" : "{$name}: PE{$start}-PE{$end}";
            $start = $end + 1;
        }

        return implode(' | ', $ranges);
    }

    private function buildCepeLivraisonPayload(Request $request): array
    {
        [$rows, $filters, $annees, $drens] = $this->getCepeRowsForLivraison($request);
        $bookData = collect($this->buildBookData($rows));

        $pagesBySubject = [
            'francais' => max(0, (int) $request->integer('pages_francais', 1)),
            'connaissances_usuelles' => max(0, (int) $request->integer('pages_connaissances_usuelles', 1)),
            'geographie' => max(0, (int) $request->integer('pages_geographie', 1)),
            'malagasy' => max(0, (int) $request->integer('pages_malagasy', 1)),
            'operation' => max(0, (int) $request->integer('pages_operation', 1)),
            'probleme' => max(0, (int) $request->integer('pages_probleme', 1)),
            'tffmom' => max(0, (int) $request->integer('pages_tffmom', 1)),
        ];

        $params = [
            'ge_par_soubique' => max(1, (int) $request->integer('ge_par_soubique', 5)),
            'enveloppes_par_barre_cire' => max(1, (int) $request->integer('enveloppes_par_barre_cire', 5)),
            'pages_par_ram' => max(1, (int) $request->integer('pages_par_ram', 500)),
            'marqueur_fixe_par_cisco' => max(0, (int) $request->integer('marqueur_fixe_par_cisco', 0)),
            'marqueur_par_soubique' => max(0, (float) $request->query('marqueur_par_soubique', 0)),
            'pe_margin_percent' => max(0, min(100, (float) $request->query('pe_margin_percent', 0))),
            'ge_margin_percent' => max(0, min(100, (float) $request->query('ge_margin_percent', 0))),
        ];

        $pagesTotalParCandidat = array_sum($pagesBySubject);
        $otherSubjectsCount = collect($pagesBySubject)
            ->except('probleme')
            ->filter(fn (int $pages) => $pages > 0)
            ->count();
        $hasProbleme = (int) ($pagesBySubject['probleme'] ?? 0) > 0;

        $livraisonRows = $bookData
            ->groupBy(fn (array $centre) => $centre['dren'].'|'.$centre['cisco'])
            ->map(function (Collection $centres, string $key) use ($params, $pagesTotalParCandidat, $otherSubjectsCount, $hasProbleme) {
                [$dren, $cisco] = explode('|', $key, 2);
                $candidats = (int) $centres->sum('total_candidats');
                $salles = (int) $centres->sum('total_salles');
                $peBase = $salles;
                $peMarginCount = (int) ceil($peBase * ($params['pe_margin_percent'] / 100));
                $pe = $peBase + $peMarginCount;
                $geBaseReference = $salles;
                $geMarginCount = (int) ceil($geBaseReference * ($params['ge_margin_percent'] / 100));
                $geMarginReference = $geBaseReference + $geMarginCount;
                $geProbleme = $hasProbleme ? (int) count($this->getGeDistribution($geMarginReference, self::TYPE_BEPC)) : 0;
                $geAutresParMatiere = $otherSubjectsCount > 0 ? (int) count($this->getGeDistribution($geMarginReference, self::TYPE_CEPE)) : 0;
                $geAutres = $geAutresParMatiere * $otherSubjectsCount;
                $ge = $geProbleme + $geAutres;
                $soubique = (int) ceil($ge / $params['ge_par_soubique']);
                $ficelle = $soubique;
                $enveloppesACirer = $pe + $ge + $soubique;
                $cire = (int) ceil($enveloppesACirer / $params['enveloppes_par_barre_cire']);
                $pagesTotal = $pagesTotalParCandidat * $candidats;
                $ram = (int) ceil($pagesTotal / $params['pages_par_ram']);
                $marqueur = $params['marqueur_fixe_par_cisco'] + (int) ceil($soubique * $params['marqueur_par_soubique']);

                return [
                    'dren' => $dren,
                    'cisco' => $cisco,
                    'candidats' => $candidats,
                    'salles' => $salles,
                    'cire' => $cire,
                    'soubique' => $soubique,
                    'pe_base' => $peBase,
                    'pe_margin_count' => $peMarginCount,
                    'pe' => $pe,
                    'ge_base_reference' => $geBaseReference,
                    'ge_margin_count' => $geMarginCount,
                    'ge_margin_reference' => $geMarginReference,
                    'ge' => $ge,
                    'ge_probleme' => $geProbleme,
                    'ge_probleme_distribution' => $hasProbleme ? $this->getGeDistribution($geMarginReference, self::TYPE_BEPC) : [],
                    'ge_autres' => $geAutres,
                    'ge_autres_par_matiere' => $geAutresParMatiere,
                    'ge_autres_distribution' => $otherSubjectsCount > 0 ? $this->getGeDistribution($geMarginReference, self::TYPE_CEPE) : [],
                    'papier_ram' => $ram,
                    'marqueur' => $marqueur,
                    'ficelle' => $ficelle,
                    'pages_total' => $pagesTotal,
                ];
            })
            ->sortBy(['dren', 'cisco'])
            ->values();

        return [
            'filters' => $filters,
            'annees' => $annees,
            'drens' => $drens,
            'pagesBySubject' => $pagesBySubject,
            'otherSubjectsCount' => $otherSubjectsCount,
            'params' => $params,
            'pagesTotalParCandidat' => $pagesTotalParCandidat,
            'rows' => $livraisonRows,
            'global' => [
                'total_candidats' => (int) $livraisonRows->sum('candidats'),
                'total_salles' => (int) $livraisonRows->sum('salles'),
                'total_cire' => (int) $livraisonRows->sum('cire'),
                'total_soubique' => (int) $livraisonRows->sum('soubique'),
                'total_pe' => (int) $livraisonRows->sum('pe'),
                'total_ge' => (int) $livraisonRows->sum('ge'),
                'total_ge_probleme' => (int) $livraisonRows->sum('ge_probleme'),
                'total_ge_autres' => (int) $livraisonRows->sum('ge_autres'),
                'total_ram' => (int) $livraisonRows->sum('papier_ram'),
                'total_marqueur' => (int) $livraisonRows->sum('marqueur'),
                'total_ficelle' => (int) $livraisonRows->sum('ficelle'),
            ],
        ];
    }

    private function buildBepcCopiesPayload(Request $request): array
    {
        $filters = [
            'annee' => (string) $request->query('annee', ''),
            'dren' => (string) $request->query('dren', ''),
            'cisco' => (string) $request->query('cisco', ''),
            'rounding_mode' => (string) $request->query('rounding_mode', 'up'),
            'add_missing_langue_surplus' => $request->boolean('add_missing_langue_surplus', false),
            'missing_langue_surplus_step' => max(1000, (int) $request->integer('missing_langue_surplus_step', 1000)),
            'isolated_centres' => collect((array) $request->query('isolated_centres', []))
                ->map(fn ($value) => (int) $value)
                ->filter(fn (int $value) => $value > 0)
                ->unique()
                ->values()
                ->all(),
            'merge_small_soubique' => $request->boolean('merge_small_soubique', true),
            'merge_small_soubique_capacity' => max(1000, (int) $request->integer('merge_small_soubique_capacity', 6000)),
        ];

        if (! in_array($filters['rounding_mode'], ['up', 'down'], true)) {
            $filters['rounding_mode'] = 'up';
        }

        $annees = DB::table('repartition_salles')
            ->select('annee')
            ->where('langue', '!=', self::CEPE_KEY)
            ->distinct()
            ->orderByDesc('annee')
            ->pluck('annee')
            ->toArray();

        if ($filters['annee'] === '' && isset($annees[0])) {
            $filters['annee'] = (string) $annees[0];
        }

        $drens = DB::table('drens')
            ->select('nom')
            ->orderBy('nom')
            ->pluck('nom')
            ->toArray();
        $defaultMarginPercent = (float) (GlobalSetting::query()->value('bepc_copy_margin_percent') ?? 5);
        $marginPercent = (float) $request->query('margin_percent', $defaultMarginPercent);
        $marginPercent = max(0, min(100, $marginPercent));
        $marginRatio = 1 + ($marginPercent / 100);

        $centreRows = DB::table('repartition_salles as rs')
            ->join('centre_ecrits as ce', 'ce.id', '=', 'rs.centre_ecrit_id')
            ->join('centre_corrections as cc', 'cc.id', '=', 'ce.centre_correction_id')
            ->join('ciscos as cs', 'cs.id', '=', 'cc.cisco_id')
            ->join('drens as d', 'd.id', '=', 'cs.dren_id')
            ->leftJoin('bepc_copy_distributions as bcd', function ($join) use ($filters) {
                $join->on('bcd.cisco_id', '=', 'cs.id');

                if ($filters['annee'] !== '') {
                    $join->where('bcd.annee', '=', $filters['annee']);
                }
            })
            ->select([
                'd.nom as dren',
                'cs.id as cisco_id',
                'cs.nom as cisco',
                'ce.id as centre_ecrit_id',
                'ce.nom as centre_ecrit',
                DB::raw('SUM(rs.effectif) as total_candidats'),
                DB::raw("SUM(CASE WHEN rs.langue IN ('Anglais', 'Allemand', 'Esp') THEN rs.effectif ELSE 0 END) as total_langues"),
                DB::raw('MAX(bcd.code_postal) as code_postal'),
            ])
            ->where('rs.langue', '!=', self::CEPE_KEY);

        if ($filters['annee'] !== '') {
            $centreRows->where('rs.annee', $filters['annee']);
        }

        if ($filters['dren'] !== '') {
            $centreRows->where('d.nom', $filters['dren']);
        }

        $ciscos = (clone $centreRows)
            ->select('cs.id', 'cs.nom')
            ->distinct()
            ->orderBy('cs.nom')
            ->get()
            ->map(fn ($row) => [
                'id' => (int) $row->id,
                'nom' => (string) $row->nom,
            ])
            ->values()
            ->all();

        if ($filters['cisco'] !== '') {
            $centreRows->where('cs.id', (int) $filters['cisco']);
        }

        $centreRows = $centreRows
            ->groupBy('d.nom', 'cs.id', 'cs.nom', 'ce.id', 'ce.nom')
            ->orderBy('d.nom')
            ->orderBy('cs.nom')
            ->orderBy('ce.nom')
            ->get()
            ->map(fn ($row) => [
                'dren' => (string) $row->dren,
                'cisco_id' => (int) $row->cisco_id,
                'cisco' => (string) $row->cisco,
                'centre_ecrit_id' => (int) $row->centre_ecrit_id,
                'centre_ecrit' => (string) $row->centre_ecrit,
                'code_postal' => (string) ($row->code_postal ?? ''),
                'total_candidats' => (int) ($row->total_candidats ?? 0),
                'total_langues' => (int) ($row->total_langues ?? 0),
            ])
            ->values();

        $allowedCentreIds = $centreRows->pluck('centre_ecrit_id')->unique();
        $filters['isolated_centres'] = collect($filters['isolated_centres'])
            ->filter(fn (int $value) => $allowedCentreIds->contains($value))
            ->values()
            ->all();

        $availableIsolatableCentres = $centreRows
            ->map(fn (array $centre) => [
                'id' => $centre['centre_ecrit_id'],
                'label' => $centre['dren'].' / '.$centre['cisco'].' / '.$centre['centre_ecrit'],
            ])
            ->unique('id')
            ->sortBy('label')
            ->values();

        $isolatedCentreIds = collect($filters['isolated_centres'])->flip();

        $rows = $centreRows
            ->groupBy('cisco_id')
            ->flatMap(function (Collection $ciscoCentres) use ($isolatedCentreIds, $marginRatio, $filters) {
                $first = $ciscoCentres->first();
                $regularCentres = $ciscoCentres
                    ->reject(fn (array $centre) => $isolatedCentreIds->has($centre['centre_ecrit_id']))
                    ->values();
                $isolatedCentres = $ciscoCentres
                    ->filter(fn (array $centre) => $isolatedCentreIds->has($centre['centre_ecrit_id']))
                    ->values();

                $builtRows = collect();

                if ($regularCentres->isNotEmpty()) {
                    $builtRows->push($this->buildBepcCopiesDistributionRow(
                        $regularCentres,
                        [
                            'dren' => (string) ($first['dren'] ?? ''),
                            'cisco_id' => (int) ($first['cisco_id'] ?? 0),
                            'cisco' => (string) ($first['cisco'] ?? ''),
                            'code_postal' => (string) ($first['code_postal'] ?? ''),
                            'row_type' => 'cisco',
                        ],
                        $marginRatio,
                        $filters
                    ));
                }

                foreach ($isolatedCentres as $centre) {
                    $builtRows->push($this->buildBepcCopiesDistributionRow(
                        collect([$centre]),
                        [
                            'dren' => (string) ($centre['dren'] ?? ''),
                            'cisco_id' => (int) ($centre['cisco_id'] ?? 0),
                            'cisco' => (string) ($centre['cisco'] ?? '').' / '.$centre['centre_ecrit'].' (isole)',
                            'code_postal' => (string) ($centre['code_postal'] ?? ''),
                            'row_type' => 'centre_isole',
                        ],
                        $marginRatio,
                        $filters
                    ));
                }

                return $builtRows;
            })
            ->sortBy(['dren', 'cisco'])
            ->values();

        return [
            'filters' => $filters,
            'annees' => $annees,
            'drens' => $drens,
            'ciscos' => $ciscos,
            'availableIsolatableCentres' => $availableIsolatableCentres,
            'marginPercent' => $marginPercent,
            'rows' => $rows,
            'globalStats' => [
                'total_ciscos' => $centreRows->pluck('cisco_id')->unique()->count(),
                'total_dispatch_rows' => $rows->count(),
                'total_candidats' => (int) $rows->sum('total_candidats'),
                'total_centres' => (int) $rows->sum('total_centres'),
                'total_centres_sans_langue' => (int) $rows->sum('centres_sans_langue'),
                'total_feuilles_double' => (int) $rows->sum('feuilles_double'),
                'total_feuilles_double_arrondies' => (int) $rows->sum('feuilles_double_arrondies'),
                'total_feuilles_simple' => (int) $rows->sum('feuilles_simple'),
                'total_feuilles_simple_arrondies' => (int) $rows->sum('feuilles_simple_arrondies'),
                'total_soubiques' => (int) $rows->sum('soubique_total'),
                'total_soubiques_mixte' => (int) $rows->sum('soubique_mixte'),
                'total_missing_langue_surplus_sheets' => (int) $rows->sum('missing_langue_surplus_sheets'),
                'ciscos_with_missing_langue' => (int) $rows->filter(fn (array $row) => (int) $row['centres_sans_langue'] > 0)->count(),
                'codes_postaux_renseignes' => (int) $rows->filter(fn (array $row) => $row['code_postal'] !== '')->count(),
            ],
        ];
    }

    private function buildBepcCopiesDistributionRow(Collection $centres, array $base, float $marginRatio, array $filters): array
    {
        $candidats = (int) $centres->sum('total_candidats');
        $totalCentres = $centres->count();
        $centresSansLangue = (int) $centres->filter(fn (array $centre) => (int) ($centre['total_langues'] ?? 0) <= 0)->count();
        $centresSansLanguePercent = $totalCentres > 0 ? round(($centresSansLangue / $totalCentres) * 100, 1) : 0;
        $feuillesDouble = (int) ceil($candidats * $marginRatio * 9);
        $feuillesSimple = (int) ceil($candidats * $marginRatio * 18);
        $feuillesDoubleArrondiesBase = $this->roundBepcSheetsToStep($feuillesDouble, 1000, (string) ($filters['rounding_mode'] ?? 'up'));
        $missingLangueSurplusApplied = (bool) ($filters['add_missing_langue_surplus'] ?? false) && $centresSansLangue > 0;
        $missingLangueSurplusSheets = $missingLangueSurplusApplied ? (int) ($filters['missing_langue_surplus_step'] ?? 1000) : 0;
        $feuillesDoubleArrondies = $feuillesDoubleArrondiesBase + $missingLangueSurplusSheets;
        $feuillesSimpleArrondies = $feuillesDoubleArrondies * 2;

        $soubiqueDouble = (int) ceil($feuillesDoubleArrondies / 3000);
        $soubiqueSimple = (int) ceil($feuillesSimpleArrondies / 6000);
        $soubiqueMixte = 0;

        if (($filters['merge_small_soubique'] ?? false) && (($feuillesDoubleArrondies * 2) + $feuillesSimpleArrondies) <= (int) ($filters['merge_small_soubique_capacity'] ?? 6000)) {
            $soubiqueDouble = 0;
            $soubiqueSimple = 0;
            $soubiqueMixte = $feuillesDoubleArrondies > 0 || $feuillesSimpleArrondies > 0 ? 1 : 0;
        }

        return [
            'dren' => (string) ($base['dren'] ?? ''),
            'cisco_id' => (int) ($base['cisco_id'] ?? 0),
            'cisco' => (string) ($base['cisco'] ?? ''),
            'code_postal' => (string) ($base['code_postal'] ?? ''),
            'row_type' => (string) ($base['row_type'] ?? 'cisco'),
            'total_candidats' => $candidats,
            'total_centres' => $totalCentres,
            'centres_sans_langue' => $centresSansLangue,
            'centres_sans_langue_percent' => $centresSansLanguePercent,
            'feuilles_double' => $feuillesDouble,
            'feuilles_double_arrondies_base' => $feuillesDoubleArrondiesBase,
            'feuilles_double_arrondies' => $feuillesDoubleArrondies,
            'feuilles_simple' => $feuillesSimple,
            'feuilles_simple_arrondies' => $feuillesSimpleArrondies,
            'soubique_feuilles_double' => $soubiqueDouble,
            'soubique_feuilles_simple' => $soubiqueSimple,
            'soubique_mixte' => $soubiqueMixte,
            'soubique_total' => $soubiqueDouble + $soubiqueSimple + $soubiqueMixte,
            'missing_langue_surplus_applied' => $missingLangueSurplusApplied,
            'missing_langue_surplus_sheets' => $missingLangueSurplusSheets,
        ];
    }

    private function roundBepcSheetsToStep(int $value, int $step, string $mode): int
    {
        if ($value <= 0 || $step <= 0) {
            return 0;
        }

        $remainder = $value % $step;
        if ($remainder === 0) {
            return $value;
        }

        if ($mode === 'down') {
            return max($step, $value - $remainder);
        }

        return $value + ($step - $remainder);
    }

    private function buildCentresSaisiePayload(Request $request): array
    {
        $selectedType = strtoupper(trim((string) $request->get('type', '')));
        if (! in_array($selectedType, [self::TYPE_BEPC, self::TYPE_CEPE], true)) {
            $selectedType = '';
        }

        $selectedRegion = trim((string) $request->get('region', ''));
        $selectedAnnee = trim((string) $request->get('annee', ''));

        $drens = DB::table('drens')
            ->orderBy('nom')
            ->pluck('nom');

        if ($selectedRegion !== '' && ! $drens->contains($selectedRegion)) {
            $selectedRegion = '';
        }

        $centresQuery = DB::table('centre_ecrits as ce')
            ->join('centre_corrections as cc', 'cc.id', '=', 'ce.centre_correction_id')
            ->join('ciscos as cs', 'cs.id', '=', 'cc.cisco_id')
            ->join('drens as d', 'd.id', '=', 'cs.dren_id')
            ->select('ce.id', 'ce.nom', 'd.nom as region', 'ce.type_examen')
            ->when($selectedType !== '', fn ($query) => $query->where('ce.type_examen', $selectedType))
            ->when($selectedRegion !== '', fn ($query) => $query->where('d.nom', $selectedRegion))
            ->orderBy('d.nom')
            ->orderBy('ce.nom');

        $centres = $centresQuery->get();

        $annees = DB::table('repartition_salles')
            ->select('annee')
            ->distinct()
            ->orderByDesc('annee')
            ->pluck('annee')
            ->values();

        $centreIdsSaisis = DB::table('repartition_salles as rs')
            ->join('centre_ecrits as ce', 'ce.id', '=', 'rs.centre_ecrit_id')
            ->join('centre_corrections as cc', 'cc.id', '=', 'ce.centre_correction_id')
            ->join('ciscos as cs', 'cs.id', '=', 'cc.cisco_id')
            ->join('drens as d', 'd.id', '=', 'cs.dren_id')
            ->when($selectedType !== '', fn ($query) => $query->where('ce.type_examen', $selectedType))
            ->when($selectedRegion !== '', fn ($query) => $query->where('d.nom', $selectedRegion))
            ->when($selectedAnnee !== '', fn ($query) => $query->where('rs.annee', $selectedAnnee))
            ->distinct()
            ->pluck('ce.id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $centresSaisis = $centres
            ->filter(fn ($centre) => in_array((int) $centre->id, $centreIdsSaisis, true))
            ->values();

        $centresNonSaisis = $centres
            ->reject(fn ($centre) => in_array((int) $centre->id, $centreIdsSaisis, true))
            ->values();

        return [
            'centresSaisis' => $centresSaisis,
            'centresNonSaisis' => $centresNonSaisis,
            'selectedType' => $selectedType,
            'selectedRegion' => $selectedRegion,
            'selectedAnnee' => $selectedAnnee,
            'annees' => $annees,
            'drens' => $drens,
            'totalCentres' => $centres->count(),
            'totalSaisis' => $centresSaisis->count(),
            'totalNonSaisis' => $centresNonSaisis->count(),
        ];
    }

    private function buildSaisieRecapPayload(Request $request): array
    {
        $selectedType = strtoupper(trim((string) $request->query('type_examen', self::TYPE_ALL)));
        if (! in_array($selectedType, [self::TYPE_ALL, self::TYPE_BEPC, self::TYPE_CEPE], true)) {
            $selectedType = self::TYPE_ALL;
        }

        $selectedDren = trim((string) $request->query('dren', ''));
        $selectedAnnee = trim((string) $request->query('annee', ''));

        $annees = DB::table('repartition_salles')
            ->select('annee')
            ->distinct()
            ->orderByDesc('annee')
            ->pluck('annee');

        $drens = DB::table('drens')
            ->orderBy('nom')
            ->pluck('nom');

        if ($selectedDren !== '' && ! $drens->contains($selectedDren)) {
            $selectedDren = '';
        }

        if ($selectedAnnee !== '' && ! $annees->contains($selectedAnnee)) {
            $selectedAnnee = '';
        }

        $centres = DB::table('centre_ecrits as ce')
            ->join('centre_corrections as cc', 'cc.id', '=', 'ce.centre_correction_id')
            ->join('ciscos as cs', 'cs.id', '=', 'cc.cisco_id')
            ->join('drens as d', 'd.id', '=', 'cs.dren_id')
            ->select([
                'ce.id',
                'ce.type_examen',
                'cs.id as cisco_id',
                'd.nom as dren',
            ])
            ->when($selectedType !== self::TYPE_ALL, fn ($query) => $query->where('ce.type_examen', $selectedType))
            ->when($selectedDren !== '', fn ($query) => $query->where('d.nom', $selectedDren))
            ->orderBy('d.nom')
            ->get();

        $saisisIds = DB::table('repartition_salles as rs')
            ->join('centre_ecrits as ce', 'ce.id', '=', 'rs.centre_ecrit_id')
            ->join('centre_corrections as cc', 'cc.id', '=', 'ce.centre_correction_id')
            ->join('ciscos as cs', 'cs.id', '=', 'cc.cisco_id')
            ->join('drens as d', 'd.id', '=', 'cs.dren_id')
            ->when($selectedAnnee !== '', fn ($query) => $query->where('rs.annee', $selectedAnnee))
            ->when($selectedType !== self::TYPE_ALL, fn ($query) => $query->where('ce.type_examen', $selectedType))
            ->when($selectedType === self::TYPE_BEPC, fn ($query) => $query->where('rs.langue', '!=', self::CEPE_KEY))
            ->when($selectedType === self::TYPE_CEPE, fn ($query) => $query->where('rs.langue', self::CEPE_KEY))
            ->when($selectedDren !== '', fn ($query) => $query->where('d.nom', $selectedDren))
            ->distinct()
            ->pluck('ce.id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $saisisLookup = array_flip($saisisIds);

        $rows = $centres
            ->groupBy('dren')
            ->map(function (Collection $drenCentres, string $dren) use ($saisisLookup) {
                $totalCentres = $drenCentres->count();
                $centresSaisis = $drenCentres
                    ->filter(fn ($centre) => isset($saisisLookup[(int) $centre->id]))
                    ->count();

                $ciscoStats = $drenCentres
                    ->groupBy('cisco_id')
                    ->map(function (Collection $ciscoCentres) use ($saisisLookup) {
                        $total = $ciscoCentres->count();
                        $saisis = $ciscoCentres
                            ->filter(fn ($centre) => isset($saisisLookup[(int) $centre->id]))
                            ->count();

                        return [
                            'total' => $total,
                            'saisis' => $saisis,
                            'restants' => max(0, $total - $saisis),
                            'complete' => $total > 0 && $saisis === $total,
                            'not_started' => $total > 0 && $saisis === 0,
                        ];
                    })
                    ->values();

                return [
                    'dren' => $dren,
                    'total_centres' => $totalCentres,
                    'centres_saisis' => $centresSaisis,
                    'centres_restants' => max(0, $totalCentres - $centresSaisis),
                    'total_ciscos' => $ciscoStats->count(),
                    'ciscos_completes' => $ciscoStats->where('complete', true)->count(),
                    'ciscos_non_demarre' => $ciscoStats->where('not_started', true)->count(),
                    'complete' => $totalCentres > 0 && $centresSaisis === $totalCentres,
                    'progress' => $totalCentres > 0 ? round(($centresSaisis / $totalCentres) * 100, 1) : 0,
                ];
            })
            ->values();

        $global = [
            'total_drens' => $rows->count(),
            'drens_completes' => $rows->where('complete', true)->count(),
            'drens_incompletes' => $rows->where('complete', false)->count(),
            'total_centres' => $rows->sum('total_centres'),
            'centres_saisis' => $rows->sum('centres_saisis'),
            'centres_restants' => $rows->sum('centres_restants'),
            'total_ciscos' => $rows->sum('total_ciscos'),
            'ciscos_non_demarre' => $rows->sum('ciscos_non_demarre'),
        ];
        $global['progress'] = $global['total_centres'] > 0
            ? round(($global['centres_saisis'] / $global['total_centres']) * 100, 1)
            : 0;

        return [
            'rows' => $rows,
            'global' => $global,
            'annees' => $annees,
            'drens' => $drens,
            'filters' => [
                'annee' => $selectedAnnee,
                'type_examen' => $selectedType,
                'dren' => $selectedDren,
            ],
        ];
    }
}
