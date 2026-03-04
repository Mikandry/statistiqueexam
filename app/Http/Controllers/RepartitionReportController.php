<?php

namespace App\Http\Controllers;

use App\Models\RepartitionSalle;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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

    public function dashboard(Request $request)
    {
        [$rows, $filters, $annees, $drens] = $this->getFilteredRows($request);
        $centresSaisieStats = $this->getCentresSaisieStats($filters);

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
            'centresSaisieStats' => $centresSaisieStats,
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
        $recapSheets = $this->buildRecapSheets($bookData);
        $centrePagesByDren = $this->paginateCentresByDren($bookData);

        return view('repartition.livre-preview', [
            'filters' => $filters,
            'annees' => $annees,
            'drens' => $drens,
            'centrePagesByDren' => $centrePagesByDren,
            'recapSheets' => $recapSheets,
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
        $forcedType = strtoupper((string) $request->query('type_examen', self::TYPE_BEPC));
        if (! in_array($forcedType, [self::TYPE_BEPC, self::TYPE_CEPE], true)) {
            $request->merge(['type_examen' => self::TYPE_BEPC]);
        }

        [$rows, $filters] = $this->getFilteredRows($request);
        $bookData = $this->buildBookData($rows);
        $totalGe = collect($bookData)->sum('ge_count');
        $totalPe = collect($bookData)->sum('pe');
        $recapSheets = $this->buildRecapSheets($bookData);
        $centrePagesByDren = $this->paginateCentresByDren($bookData);

        return response()->view('repartition.livre-preview', [
            'filters' => $filters,
            'annees' => [],
            'drens' => [],
            'centrePagesByDren' => $centrePagesByDren,
            'recapSheets' => $recapSheets,
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

    public function livreExcel(Request $request)
    {
        [$rows, $filters] = $this->getFilteredRows($request);
        $bookData = collect($this->buildBookData($rows));
        $recapSheets = collect($this->buildRecapSheets($bookData->all()));

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
            ];
            $recapSheet->fromArray($recapHeaders, null, 'A1');

            $recapRow = 2;
            foreach ($recapSheets as $recap) {
                foreach (($recap['rows'] ?? []) as $line) {
                    $recapSheet->fromArray([
                        $recap['dren'],
                        $line['cisco'],
                        $line['centre_correction'],
                        $line['centre_ecrit'],
                        (int) $line['candidats'],
                        (int) $line['pe'],
                        (int) $line['ge_total'],
                        (string) ($line['ge_repartition'] ?? ''),
                    ], null, "A{$recapRow}");
                    $recapRow++;
                }
            }

            $this->styleSheetWithHeader($recapSheet);
        }

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
                'TOTAL SALLE',
                'PE',
                'GE TOTAL',
                'REPARTITION GE',
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
                ->map(function (Collection $salleRows) use ($bookData) {
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
                        'total_salle' => (int) $salleRows->sum('effectif'),
                        'pe' => (int) ($centre['pe'] ?? 0),
                        'ge_count' => (int) ($centre['ge_count'] ?? 0),
                        'ge_distribution' => implode('+', array_map(fn (int $n) => (string) $n, $centre['ge_distribution'] ?? [])),
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
                    $line['total_salle'],
                    $firstSalleForCentre ? $line['pe'] : 0,
                    $firstSalleForCentre ? $line['ge_count'] : 0,
                    $firstSalleForCentre ? $line['ge_distribution'] : '',
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
            foreach ($bookData as $centre) {
                if (($filters['type_examen'] ?? self::TYPE_ALL) === self::TYPE_BEPC && ($centre['type_examen'] ?? '') !== self::TYPE_BEPC) {
                    continue;
                }

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

                foreach (($centre['tables'] ?? []) as $table) {
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

                $pdfLikeRow++;
            }

            $pdfLikeLastCol = $pdfLikeSheet->getHighestColumn();
            $pdfLikeLastColIndex = Coordinate::columnIndexFromString($pdfLikeLastCol);
            for ($i = 1; $i <= $pdfLikeLastColIndex; $i++) {
                $pdfLikeSheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
            }
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
            ['Francais', $pagesBySubject['francais'], 'Connaissances usuelles (CU)', $pagesBySubject['connaissances_usuelles'], 'Geographie', $pagesBySubject['geographie']],
            ['Malagasy', $pagesBySubject['malagasy'], 'Operation', $pagesBySubject['operation'], 'Probleme', $pagesBySubject['probleme']],
            ['TFFMOM', $pagesBySubject['tffmom'], 'Total pages/candidat', $payload['pagesTotalParCandidat']],
            ['DREN', 'CISCO', 'Candidats', 'Salles', 'PE', 'GE', 'Soubique', 'Ficelle', 'Cire', 'Pages total', 'Papier RAM', 'Marqueur'],
        ], null, 'A1');

        $sheet->mergeCells('A1:L1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $rowIndex = 9;
        foreach ($rows as $row) {
            $sheet->fromArray([[
                $row['dren'],
                $row['cisco'],
                (int) $row['candidats'],
                (int) $row['salles'],
                (int) $row['pe'],
                (int) $row['ge'],
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
            (int) $global['total_soubique'],
            (int) $global['total_ficelle'],
            (int) $global['total_cire'],
            '',
            (int) $global['total_ram'],
            (int) $global['total_marqueur'],
        ]], null, "A{$rowIndex}");

        $sheet->getStyle("A8:L8")->getFont()->setBold(true);
        $sheet->getStyle("A8:L8")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');
        $sheet->getStyle("A8:L{$rowIndex}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$rowIndex}:L{$rowIndex}")->getFont()->setBold(true);
        for ($i = 1; $i <= 12; $i++) {
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

        $centreRows = $this->buildStatsExportRows($rows, $filters['type_examen']);
        [$headers, $numericKeys] = $this->getStatsExportMeta($filters['type_examen']);

        $csvRows = [$headers];
        $grandTotal = array_fill_keys($numericKeys, 0);

        foreach ($centreRows->groupBy('dren') as $dren => $drenRows) {
            $drenTotal = [];
            foreach ($numericKeys as $key) {
                $drenTotal[$key] = (int) $drenRows->sum($key);
            }

            foreach ($drenRows as $line) {
                $csvRows[] = $this->lineToStatsCsvRow($line, $filters['type_examen']);
            }

            $csvRows[] = $this->buildStatsTotalRow("TOTAL DREN {$dren}", $drenTotal, $filters['type_examen']);
            $csvRows[] = [''];

            foreach ($numericKeys as $key) {
                $grandTotal[$key] += $drenTotal[$key];
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
        $recapHeaders = ['DREN', 'CISCO', 'CENTRE CORRECTION', 'CENTRE ECRIT', 'ALL', 'ESP', 'ANG', 'B', 'SALLE', 'TOTAL'];
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
                    'salle' => $this->countDistinctSalles($group),
                    'total' => (int) $group->sum('effectif'),
                ];
            })
            ->sortBy(['dren', 'cisco', 'centre_correction', 'centre_ecrit'])
            ->values();

        $recapRow = 2;
        foreach ($centreRecapRows as $line) {
            $recapSheet->fromArray([
                $line['dren'],
                $line['cisco'],
                $line['centre_correction'],
                $line['centre_ecrit'],
                $line['all'],
                $line['esp'],
                $line['ang'],
                $line['b'],
                $line['salle'],
                $line['total'],
            ], null, "A{$recapRow}");
            $recapRow++;
        }
        $recapSheet->getStyle('A1:J1')->getFont()->setBold(true);
        $recapSheet->getStyle('A1:J1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');
        $recapTableLastRow = max(1, $recapRow - 1);
        $recapSheet->getStyle("A1:J{$recapTableLastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        foreach (range('A', 'J') as $col) {
            $recapSheet->getColumnDimension($col)->setAutoSize(true);
        }

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

        return view('repartition.dispatching-preview', [
            'filters' => $filters,
            'annees' => $annees,
            'drens' => $drens,
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
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        $usedSheetNames = [];
        if ($dispatchRows->isEmpty()) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle('DISPATCHING');
            $sheet->setCellValue('A1', 'Aucune donnée pour les filtres sélectionnés.');
        }

        foreach ($dispatchRows->groupBy('axe_dispatching') as $axe => $axeRows) {
            $sheet = $spreadsheet->createSheet();
            $sheetName = $this->makeExcelSheetName($axe, $usedSheetNames);
            $sheet->setTitle($sheetName);

            $sheet->fromArray([
                ['DISPATCHING '.$filters['type_examen'].' '.($filters['annee'] !== '' ? $filters['annee'] : date('Y'))],
                ['AXE: '.$axe],
                ['DREN', 'CISCO', 'CENTRE CORRECTION', 'CENTRE D\'ECRIT', 'CODE', 'ESP', 'ALL', 'ANG', 'B', 'S', 'TOTAL'],
            ], null, 'A1');
            $sheet->mergeCells('A1:K1');
            $sheet->mergeCells('A2:K2');

            $rowIndex = 4;
            foreach ($axeRows->groupBy('point_largage') as $point => $pointRows) {
                $sheet->setCellValue("A{$rowIndex}", 'POINT DE LARGAGE: '.$point);
                $sheet->mergeCells("A{$rowIndex}:K{$rowIndex}");
                $rowIndex++;

                foreach ($pointRows as $line) {
                    $sheet->fromArray([[
                        $line['dren'],
                        $line['cisco'],
                        $line['centre_correction'],
                        $line['centre_ecrit'],
                        $line['code_centre'],
                        (int) $line['esp'],
                        (int) $line['allemand'],
                        (int) $line['anglais'],
                        (int) $line['option_b'],
                        (int) $line['salles'],
                        (int) $line['candidats'],
                    ]], null, "A{$rowIndex}");
                    $rowIndex++;
                }

                $sheet->fromArray([[
                    'TOTAL POINT '.$point,
                    '',
                    '',
                    '',
                    '',
                    (int) $pointRows->sum('esp'),
                    (int) $pointRows->sum('allemand'),
                    (int) $pointRows->sum('anglais'),
                    (int) $pointRows->sum('option_b'),
                    (int) $pointRows->sum('salles'),
                    (int) $pointRows->sum('candidats'),
                ]], null, "A{$rowIndex}");
                $rowIndex++;
            }

            $sheet->fromArray([[
                'TOTAL AXE '.$axe,
                '',
                '',
                '',
                '',
                (int) $axeRows->sum('esp'),
                (int) $axeRows->sum('allemand'),
                (int) $axeRows->sum('anglais'),
                (int) $axeRows->sum('option_b'),
                (int) $axeRows->sum('salles'),
                (int) $axeRows->sum('candidats'),
            ]], null, "A{$rowIndex}");
            $rowIndex++;
            $sheet->setCellValue("A{$rowIndex}", 'ESP: Espagnol, ALL: Allemand, ANG: Anglais, B: Option B, S: Salles');
            $sheet->mergeCells("A{$rowIndex}:K{$rowIndex}");

            foreach (range('A', 'K') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $headerRange = 'A3:K3';
            $lastDataRow = $rowIndex;
            $tableRange = "A3:K{$lastDataRow}";
            $sheet->getStyle('A1:K2')->getFont()->setBold(true);
            $sheet->getStyle('A1:K2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($headerRange)->getFont()->setBold(true);
            $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');
            $sheet->getStyle($tableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        $spreadsheet->setActiveSheetIndex(0);
        $writer = new Xlsx($spreadsheet);
        $fileName = 'dispatching_'.($filters['annee'] !== '' ? $filters['annee'].'_' : '').strtolower($filters['type_examen']).'.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function buildStatsExportRows(Collection $rows, string $typeExamen): Collection
    {
        return $rows
            ->groupBy(fn ($row) => implode('|', [
                $row->dren,
                $row->cisco,
                $row->centre_correction,
                $row->centre_ecrit,
                $row->type_examen,
                $row->numero_salle,
            ]))
            ->map(function (Collection $group) {
                $first = $group->first();
                $sum = fn (string $langue): int => (int) $group->where('langue', $langue)->sum('effectif');
                $sumStartsWith = fn (string $prefix): int => (int) $group->filter(fn ($row) => str_starts_with((string) $row->langue, $prefix))->sum('effectif');

                $anglais = $sum('Anglais');
                $esp = $sum('Esp');
                $allemand = $sum('Allemand');
                $optionB = $sum('Option B');
                $etrangerOptionA = $sumStartsWith('Etranger Option A');
                $etrangerOptionB = $sumStartsWith('Etranger Option B');
                $totalCepe = $sum(self::CEPE_KEY);

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
                'DREN', 'CISCO', 'CENTRE CORRECTION', 'CENTRE ECRIT', 'SALLE', 'TOTAL CEPE',
            ], ['total_cepe']];
        }

        if ($typeExamen === self::TYPE_BEPC) {
            return [[
                'DREN', 'CISCO', 'CENTRE CORRECTION', 'CENTRE ECRIT', 'SALLE',
                'OPTION A - ANGLAIS', 'OPTION A - ESP', 'OPTION A - ALLEMAND', 'OPTION B',
                'ETRANGER OPTION A', 'ETRANGER OPTION B', 'TOTAL',
            ], ['anglais', 'esp', 'allemand', 'option_b', 'etranger_option_a', 'etranger_option_b', 'total']];
        }

        return [[
            'DREN', 'CISCO', 'CENTRE CORRECTION', 'CENTRE ECRIT', 'TYPE EXAMEN', 'SALLE',
            'OPTION A - ANGLAIS', 'OPTION A - ESP', 'OPTION A - ALLEMAND', 'OPTION B',
            'ETRANGER OPTION A', 'ETRANGER OPTION B', 'TOTAL CEPE', 'TOTAL',
        ], ['anglais', 'esp', 'allemand', 'option_b', 'etranger_option_a', 'etranger_option_b', 'total_cepe', 'total']];
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
            $line['total'],
        ];
    }

    private function buildStatsTotalRow(string $label, array $totals, string $typeExamen): array
    {
        if ($typeExamen === self::TYPE_CEPE) {
            return [$label, '', '', '', '', $totals['total_cepe'] ?? 0];
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
        $centresQuery = DB::table('centre_ecrits as ce')
            ->join('centre_corrections as cc', 'cc.id', '=', 'ce.centre_correction_id')
            ->join('ciscos as cs', 'cs.id', '=', 'cc.cisco_id')
            ->join('drens as d', 'd.id', '=', 'cs.dren_id');

        if ($filters['dren'] !== '') {
            $centresQuery->where('d.nom', $filters['dren']);
        }
        if ($filters['type_examen'] === self::TYPE_BEPC || $filters['type_examen'] === self::TYPE_CEPE) {
            $centresQuery->where('ce.type_examen', $filters['type_examen']);
        }

        $totalCentres = (int) $centresQuery->distinct('ce.id')->count('ce.id');

        $saisisQuery = DB::table('repartition_salles as rs')
            ->join('centre_ecrits as ce', 'ce.id', '=', 'rs.centre_ecrit_id')
            ->join('centre_corrections as cc', 'cc.id', '=', 'ce.centre_correction_id')
            ->join('ciscos as cs', 'cs.id', '=', 'cc.cisco_id')
            ->join('drens as d', 'd.id', '=', 'cs.dren_id');

        if ($filters['annee'] !== '') {
            $saisisQuery->where('rs.annee', $filters['annee']);
        }
        if ($filters['dren'] !== '') {
            $saisisQuery->where('d.nom', $filters['dren']);
        }
        if ($filters['type_examen'] === self::TYPE_BEPC) {
            $saisisQuery->where('rs.langue', '!=', self::CEPE_KEY);
        } elseif ($filters['type_examen'] === self::TYPE_CEPE) {
            $saisisQuery->where('rs.langue', self::CEPE_KEY);
        }

        $centresSaisis = (int) $saisisQuery->distinct('ce.id')->count('ce.id');
        $centresNonSaisis = max(0, $totalCentres - $centresSaisis);

        return [
            'total' => $totalCentres,
            'saisis' => $centresSaisis,
            'non_saisis' => $centresNonSaisis,
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
                        'candidats' => (int) $centre['total_candidats'],
                        'salles' => (int) $centre['total_salles'],
                        'pe' => (int) $centre['total_salles'],
                        'ge_total' => (int) $centre['ge_count'],
                        'ge_repartition' => implode('+', array_map(fn (int $n) => (string) $n, $centre['ge_distribution'] ?? [])),
                    ];
                })->values();

                return [
                    'dren' => $dren,
                    'rows' => $rows->all(),
                    'total_centres' => $rows->count(),
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
                $pages = [];
                $currentPage = [];

                foreach ($drenCentres->values()->all() as $centre) {
                    if ($currentPage === []) {
                        $currentPage[] = $centre;
                        continue;
                    }

                    $first = $currentPage[0];
                    $canPair = count($currentPage) === 1
                        && ((int) ($first['pe'] ?? 0)) <= self::MAX_SALLES_PER_TABLE
                        && ((int) ($centre['pe'] ?? 0)) <= self::MAX_SALLES_PER_TABLE;

                    if ($canPair) {
                        $currentPage[] = $centre;
                        $pages[] = $currentPage;
                        $currentPage = [];
                        continue;
                    }

                    $pages[] = $currentPage;
                    $currentPage = [$centre];
                }

                if ($currentPage !== []) {
                    $pages[] = $currentPage;
                }

                return [
                    'dren' => $dren,
                    'pages' => $pages,
                ];
            })
            ->values()
            ->toArray();
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
        ];

        $pagesTotalParCandidat = array_sum($pagesBySubject);

        $livraisonRows = $bookData
            ->groupBy(fn (array $centre) => $centre['dren'].'|'.$centre['cisco'])
            ->map(function (Collection $centres, string $key) use ($params, $pagesTotalParCandidat) {
                [$dren, $cisco] = explode('|', $key, 2);
                $candidats = (int) $centres->sum('total_candidats');
                $salles = (int) $centres->sum('total_salles');
                $pe = $salles;
                $ge = (int) $centres->sum('ge_count');
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
                    'pe' => $pe,
                    'ge' => $ge,
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
                'total_ram' => (int) $livraisonRows->sum('papier_ram'),
                'total_marqueur' => (int) $livraisonRows->sum('marqueur'),
                'total_ficelle' => (int) $livraisonRows->sum('ficelle'),
            ],
        ];
    }
}
