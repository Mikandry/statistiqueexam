<?php

namespace App\Http\Controllers;

use App\Models\RepartitionSalle;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RepartitionReportController extends Controller
{
    private const TYPE_ALL = 'ALL';

    private const TYPE_BEPC = 'BEPC';

    private const TYPE_CEPE = 'CEPE';

    private const CEPE_KEY = 'TOTAL';

    private const MAX_SALLES_PER_TABLE = 25;

    private const CENTRES_PER_PAGE = 2;

    public function dashboard(Request $request)
    {
        [$rows, $filters, $annees, $drens] = $this->getFilteredRows($request);

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

        $langueOptionChart = $totalsByLangue
            ->map(fn (int $value, string $label) => [
                'label' => $label,
                'value' => $value,
            ])
            ->values();

        return view('repartition.dashboard', [
            'rows' => $rows,
            'filters' => $filters,
            'annees' => $annees,
            'drens' => $drens,
            'totalsByLangue' => $totalsByLangue,
            'langueOptionChart' => $langueOptionChart,
            'recapByDren' => $recapByDrenPaginated,
            'chartData' => $chartData,
            'globalStats' => [
                'total_candidats' => $rows->sum('effectif'),
                'total_salles' => $this->countDistinctSalles($rows),
                'total_drens' => $rows->pluck('dren')->unique()->count(),
                'total_ciscos' => $rows->pluck('cisco')->unique()->count(),
                'total_centres_correction' => $rows->pluck('centre_correction')->unique()->count(),
                'total_centres_ecrit' => $rows->pluck('centre_ecrit')->unique()->count(),
            ],
        ]);
    }

    public function livrePreview(Request $request)
    {
        [$rows, $filters, $annees, $drens] = $this->getFilteredRows($request);
        $bookData = $this->buildBookData($rows);
        $totalGe = collect($bookData)->sum('ge_count');
        $totalPe = collect($bookData)->sum('pe');
        $booksByDren = $this->buildBooksByDren($rows, $bookData);

        return view('repartition.livre-preview', [
            'filters' => $filters,
            'annees' => $annees,
            'drens' => $drens,
            'booksByDren' => $booksByDren,
            'globalStats' => [
                'total_candidats' => $rows->sum('effectif'),
                'total_salles' => $this->countDistinctSalles($rows),
                'total_pe' => $totalPe,
                'total_ge' => $totalGe,
                'totaux_langues' => $rows
                    ->groupBy(fn ($row) => $row->langue === self::CEPE_KEY ? 'Total CEPE' : $row->langue)
                    ->map(fn (Collection $group) => $group->sum('effectif'))
                    ->sortDesc(),
            ],
            'pdfMode' => false,
        ]);
    }

    public function livrePdf(Request $request)
    {
        [$rows, $filters] = $this->getFilteredRows($request);
        $bookData = $this->buildBookData($rows);
        $totalGe = collect($bookData)->sum('ge_count');
        $totalPe = collect($bookData)->sum('pe');
        $booksByDren = $this->buildBooksByDren($rows, $bookData);

        return response()->view('repartition.livre-preview', [
            'filters' => $filters,
            'annees' => [],
            'drens' => [],
            'booksByDren' => $booksByDren,
            'globalStats' => [
                'total_candidats' => $rows->sum('effectif'),
                'total_salles' => $this->countDistinctSalles($rows),
                'total_pe' => $totalPe,
                'total_ge' => $totalGe,
                'totaux_langues' => $rows
                    ->groupBy(fn ($row) => $row->langue === self::CEPE_KEY ? 'Total CEPE' : $row->langue)
                    ->map(fn (Collection $group) => $group->sum('effectif'))
                    ->sortDesc(),
            ],
            'pdfMode' => true,
        ]);
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

    public function exportExcel(Request $request)
    {
        [$rows, $filters] = $this->getFilteredRows($request);

        $centreRows = $rows
            ->groupBy(fn ($row) => implode('|', [$row->dren, $row->cisco, $row->centre_correction, $row->centre_ecrit]))
            ->map(function (Collection $group) {
                $sum = function (string $langue) use ($group): int {
                    return (int) $group->where('langue', $langue)->sum('effectif');
                };

                $first = $group->first();
                $anglais = $sum('Anglais');
                $esp = $sum('Esp');
                $allemand = $sum('Allemand');
                $optionB = $sum('Option B');
                $e = $sum(self::CEPE_KEY);

                return [
                    'dren' => $first->dren,
                    'cisco' => $first->cisco,
                    'centre_correction' => $first->centre_correction,
                    'centre_ecrit' => $first->centre_ecrit,
                    'anglais' => $anglais,
                    'esp' => $esp,
                    'allemand' => $allemand,
                    'option_b' => $optionB,
                    'e' => $e,
                    'total' => $anglais + $esp + $allemand + $optionB + $e,
                ];
            })
            ->sortBy(['dren', 'cisco', 'centre_correction', 'centre_ecrit'])
            ->values();

        $csvRows = [];
        $csvRows[] = [
            'DREN',
            'CISCO',
            'CENTRE CORRECTION',
            'CENTRE ECRIT',
            'OPTION A - ANGLAIS',
            'OPTION A - ESP',
            'OPTION A - ALLEMAND',
            'OPTION B',
            'E',
            'TOTAL',
        ];

        $grandTotal = [
            'anglais' => 0,
            'esp' => 0,
            'allemand' => 0,
            'option_b' => 0,
            'e' => 0,
            'total' => 0,
        ];

        foreach ($centreRows->groupBy('dren') as $dren => $drenRows) {
            $drenTotal = [
                'anglais' => (int) $drenRows->sum('anglais'),
                'esp' => (int) $drenRows->sum('esp'),
                'allemand' => (int) $drenRows->sum('allemand'),
                'option_b' => (int) $drenRows->sum('option_b'),
                'e' => (int) $drenRows->sum('e'),
                'total' => (int) $drenRows->sum('total'),
            ];

            foreach ($drenRows as $line) {
                $csvRows[] = [
                    $line['dren'],
                    $line['cisco'],
                    $line['centre_correction'],
                    $line['centre_ecrit'],
                    $line['anglais'],
                    $line['esp'],
                    $line['allemand'],
                    $line['option_b'],
                    $line['e'],
                    $line['total'],
                ];
            }

            $csvRows[] = [
                "TOTAL DREN {$dren}",
                '',
                '',
                '',
                $drenTotal['anglais'],
                $drenTotal['esp'],
                $drenTotal['allemand'],
                $drenTotal['option_b'],
                $drenTotal['e'],
                $drenTotal['total'],
            ];
            $csvRows[] = [''];

            foreach ($grandTotal as $key => $value) {
                $grandTotal[$key] = $value + $drenTotal[$key];
            }
        }

        $csvRows[] = [
            'TOTAL GLOBAL',
            '',
            '',
            '',
            $grandTotal['anglais'],
            $grandTotal['esp'],
            $grandTotal['allemand'],
            $grandTotal['option_b'],
            $grandTotal['e'],
            $grandTotal['total'],
        ];

        $fileName = 'recap_repartition_'.($filters['annee'] !== '' ? $filters['annee'].'_' : '').strtolower($filters['type_examen']).'.csv';

        return response()->streamDownload(function () use ($csvRows) {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\xEF\xBB\xBF");
            foreach ($csvRows as $row) {
                fputcsv($output, $row, ';');
            }
            fclose($output);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportDispatchingExcel(Request $request)
    {
        [$rows, $filters] = $this->getFilteredRows($request);

        $centreRows = $rows
            ->groupBy(fn ($row) => implode('|', [$row->centre_ecrit_id, $row->annee, $row->type_examen]))
            ->map(function (Collection $group) {
                $first = $group->first();

                return [
                    'axe_dispatching' => trim((string) ($first->axe_dispatching ?? '')) ?: 'AXE NON RENSEIGNE',
                    'point_largage' => trim((string) ($first->point_largage ?? '')) ?: 'POINT NON RENSEIGNE',
                    'centre_ecrit' => $first->centre_ecrit,
                    'candidats' => (int) $group->sum('effectif'),
                    'salles' => $this->countDistinctSalles($group),
                ];
            })
            ->sortBy(['axe_dispatching', 'point_largage', 'centre_ecrit'])
            ->values();

        $xmlEscape = fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
        $buildCell = function (string $value, string $type = 'String', string $style = 'Text', ?int $mergeAcross = null) use ($xmlEscape): string {
            $merge = $mergeAcross !== null ? ' ss:MergeAcross="'.$mergeAcross.'"' : '';

            return '<Cell ss:StyleID="'.$style.'"'.$merge.'><Data ss:Type="'.$type.'">'.$xmlEscape($value).'</Data></Cell>';
        };

        $buildNumberCell = fn (int $value, string $style = 'Number'): string => $buildCell((string) $value, 'Number', $style);
        $buildEmptyCell = fn (): string => '<Cell ss:StyleID="Text"><Data ss:Type="String"></Data></Cell>';
        $buildRow = fn (array $cells): string => '<Row>'.implode('', $cells).'</Row>';

        $workbook = [];
        $workbook[] = '<?xml version="1.0" encoding="UTF-8"?>';
        $workbook[] = '<?mso-application progid="Excel.Sheet"?>';
        $workbook[] = '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:x="urn:schemas-microsoft-com:office:excel">';
        $workbook[] = '<Styles>';
        $workbook[] = '<Style ss:ID="Default" ss:Name="Normal"><Alignment ss:Vertical="Center"/></Style>';
        $workbook[] = '<Style ss:ID="Title"><Font ss:Bold="1" ss:Size="12"/><Alignment ss:Horizontal="Center" ss:Vertical="Center"/></Style>';
        $workbook[] = '<Style ss:ID="Header"><Font ss:Bold="1"/><Interior ss:Color="#E2E8F0" ss:Pattern="Solid"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>';
        $workbook[] = '<Style ss:ID="Text"><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>';
        $workbook[] = '<Style ss:ID="Number"><Alignment ss:Horizontal="Right"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>';
        $workbook[] = '<Style ss:ID="Footer"><Font ss:Italic="1"/><Alignment ss:Horizontal="Center"/></Style>';
        $workbook[] = '</Styles>';

        $usedSheetNames = [];
        foreach ($centreRows->groupBy('axe_dispatching') as $axe => $axeRows) {
            $sheetName = $this->makeExcelSheetName($axe, $usedSheetNames);
            $workbook[] = '<Worksheet ss:Name="'.$xmlEscape($sheetName).'">';
            $workbook[] = '<Table>';
            $workbook[] = '<Column ss:Width="180"/><Column ss:Width="180"/><Column ss:Width="240"/><Column ss:Width="120"/><Column ss:Width="110"/><Column ss:Width="130"/><Column ss:Width="160"/><Column ss:Width="170"/>';
            $workbook[] = $buildRow([$buildCell('ORGANISATION DES EXAMENS SOE', 'String', 'Title', 7)]);
            $workbook[] = $buildRow([$buildCell('AXE: '.$axe, 'String', 'Header', 7)]);
            $workbook[] = $buildRow([
                $buildCell('AXE DE DISPATCHING', 'String', 'Header'),
                $buildCell('POINT DE LARGAGE', 'String', 'Header'),
                $buildCell('NOM DU CENTRE', 'String', 'Header'),
                $buildCell('NOMBRE DE CANDIDATS', 'String', 'Header'),
                $buildCell('TOTAL SALLES', 'String', 'Header'),
                $buildCell('NOMBRE DE SOUBIQUE', 'String', 'Header'),
                $buildCell('INSTRUCTIONS', 'String', 'Header'),
                $buildCell('OBSERVATION', 'String', 'Header'),
            ]);

            foreach ($axeRows->groupBy('point_largage') as $point => $pointRows) {
                $workbook[] = $buildRow([$buildCell('POINT DE LARGAGE: '.$point, 'String', 'Header', 7)]);

                foreach ($pointRows as $line) {
                    $workbook[] = $buildRow([
                        $buildCell($line['axe_dispatching']),
                        $buildCell($line['point_largage']),
                        $buildCell($line['centre_ecrit']),
                        $buildNumberCell((int) $line['candidats']),
                        $buildNumberCell((int) $line['salles']),
                        $buildEmptyCell(),
                        $buildEmptyCell(),
                        $buildEmptyCell(),
                    ]);
                }

                $workbook[] = $buildRow([
                    $buildCell(''),
                    $buildCell('TOTAL '.$point, 'String', 'Header'),
                    $buildCell('', 'String', 'Header'),
                    $buildNumberCell((int) $pointRows->sum('candidats'), 'Header'),
                    $buildNumberCell((int) $pointRows->sum('salles'), 'Header'),
                    $buildCell('', 'String', 'Header'),
                    $buildCell('', 'String', 'Header'),
                    $buildCell('', 'String', 'Header'),
                ]);
            }

            $workbook[] = $buildRow([
                $buildCell('TOTAL AXE '.$axe, 'String', 'Header', 2),
                $buildNumberCell((int) $axeRows->sum('candidats'), 'Header'),
                $buildNumberCell((int) $axeRows->sum('salles'), 'Header'),
                $buildCell('', 'String', 'Header'),
                $buildCell('', 'String', 'Header'),
                $buildCell('', 'String', 'Header'),
            ]);
            $workbook[] = $buildRow([$buildCell('RAMAROSON Andry Michael, 0340604716, all copyright', 'String', 'Footer', 7)]);
            $workbook[] = '</Table>';
            $workbook[] = '<WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel"><PageSetup><Header x:Data="&amp;COrganisation des examens SOE"/><Footer x:Data="&amp;LRAMAROSON Andry Michael, 0340604716, all copyright"/></PageSetup></WorksheetOptions>';
            $workbook[] = '</Worksheet>';
        }
        $workbook[] = '</Workbook>';

        $fileName = 'dispatching_axes_points_'.($filters['annee'] !== '' ? $filters['annee'].'_' : '').strtolower($filters['type_examen']).'.xml';

        return response()->streamDownload(function () use ($workbook) {
            echo implode('', $workbook);
        }, $fileName, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
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

        $filters = [
            'annee' => (string) $request->query('annee', ''),
            'type_examen' => strtoupper((string) $request->query('type_examen', self::TYPE_ALL)),
            'dren' => (string) $request->query('dren', ''),
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

        if ($filters['type_examen'] === self::TYPE_BEPC) {
            $query->where('rs.langue', '!=', self::CEPE_KEY);
        } elseif ($filters['type_examen'] === self::TYPE_CEPE) {
            $query->where('rs.langue', self::CEPE_KEY);
        }

        $rows = $query->get()->map(function ($row) {
            $row->type_examen = $row->langue === self::CEPE_KEY ? self::TYPE_CEPE : self::TYPE_BEPC;

            return $row;
        });

        return [$rows, $filters, $annees, $drens];
    }

    private function buildBookData(Collection $rows): array
    {
        $langueOrder = array_flip(RepartitionSalle::LANGUES);

        return $rows
            ->groupBy(fn ($row) => $row->centre_ecrit_id.'|'.$row->annee.'|'.$row->type_examen)
            ->map(function (Collection $centreRows) use ($langueOrder) {
                $first = $centreRows->first();
                $salles = $centreRows->pluck('numero_salle')->unique()->sort()->values();
                $salleChunks = $salles->chunk(self::MAX_SALLES_PER_TABLE);

                $rowsByLangue = $centreRows->groupBy('langue');
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
                if ($labels->isEmpty()) {
                    $labels = $rowsByLangue->keys()
                        ->map(fn (string $langue) => $langue === self::CEPE_KEY ? 'Total CEPE' : $langue)
                        ->take(1)
                        ->values();
                }

                $tables = $salleChunks->map(function (Collection $chunk, int $index) use ($labels, $rowsByLangue) {
                    $tableRows = $labels->map(function (string $label) use ($chunk, $rowsByLangue) {
                        $langueKey = $label === 'Total CEPE' ? self::CEPE_KEY : $label;
                        $langueRows = $rowsByLangue->get($langueKey, collect())->keyBy('numero_salle');

                        return [
                            'label' => $label,
                            'values' => $chunk->mapWithKeys(function (int $salle) use ($langueRows) {
                                return [$salle => (int) optional($langueRows->get($salle))->effectif];
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

                $pe = $this->countDistinctSalles($centreRows);
                $geDistribution = $this->getGeDistribution($pe);

                return [
                    'dren' => $first->dren,
                    'cisco' => $first->cisco,
                    'centre_correction' => $first->centre_correction,
                    'centre_ecrit' => $first->centre_ecrit,
                    'axe_dispatching' => trim((string) ($first->axe_dispatching ?? '')) ?: ($first->dren.' > '.$first->cisco.' > '.$first->centre_correction.' > '.$first->centre_ecrit),
                    'point_largage' => trim((string) ($first->point_largage ?? '')),
                    'annee' => $first->annee,
                    'type_examen' => $first->type_examen,
                    'total_candidats' => $centreRows->sum('effectif'),
                    'total_salles' => $pe,
                    'pe' => $pe,
                    'ge_count' => count($geDistribution),
                    'ge_distribution' => $geDistribution,
                    'tables' => $tables,
                ];
            })
            ->values()
            ->toArray();
    }

    private function buildBooksByDren(Collection $rows, array $bookData): array
    {
        $centresByDren = collect($bookData)->groupBy('dren');

        return $rows
            ->groupBy('dren')
            ->map(function (Collection $drenRows, string $drenName) use ($centresByDren) {
                $totauxLangues = $drenRows
                    ->groupBy(fn ($row) => $row->langue === self::CEPE_KEY ? 'Total CEPE' : $row->langue)
                    ->map(fn (Collection $group) => $group->sum('effectif'))
                    ->filter(fn (int $value) => $value > 0)
                    ->sortDesc();

                $centres = ($centresByDren->get($drenName, collect()))->values()->all();

                return [
                    'dren' => $drenName,
                    'recap' => [
                        'total_candidats' => (int) $drenRows->sum('effectif'),
                        'total_salles' => $this->countDistinctSalles($drenRows),
                        'total_centres' => $drenRows
                            ->map(fn ($row) => $row->centre_ecrit_id.'|'.$row->annee.'|'.$row->type_examen)
                            ->unique()
                            ->count(),
                        'total_pe' => collect($centres)->sum('pe'),
                        'total_ge' => collect($centres)->sum('ge_count'),
                        'totaux_langues' => $totauxLangues,
                    ],
                    'pages' => array_chunk($centres, self::CENTRES_PER_PAGE),
                ];
            })
            ->values()
            ->toArray();
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

    private function getGeDistribution(int $pe): array
    {
        if ($pe <= 0) {
            return [];
        }

        $distribution = array_fill(0, intdiv($pe, 3), 3);
        $reste = $pe % 3;

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

    private function countDistinctSalles(Collection $rows): int
    {
        return $rows
            ->map(fn ($row) => $row->centre_ecrit_id.'|'.$row->annee.'|'.$row->type_examen.'|'.$row->numero_salle)
            ->unique()
            ->count();
    }
}
