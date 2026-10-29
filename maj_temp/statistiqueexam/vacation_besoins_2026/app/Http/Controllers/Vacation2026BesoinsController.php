<?php

namespace App\Http\Controllers;

use App\Models\Dren;
use App\Models\Vacation2026Bareme;
use App\Models\Vacation2026Parametre;
use App\Services\VacationBesoinsService;
use App\Support\MadagascarProvinces;
use App\Support\VacationDecreeCatalogue as Cat;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Vacation 2026 — Besoins & Montants.
 *
 * Affiche, sans import ni affectation d'agents, le nombre de personnes mobilisées par activité
 * (MEN central, DREN, CISCO, centres d'écrit, centres de correction, transcription, EPS), par examen,
 * et le montant correspondant : total Madagascar, par DREN, par CISCO, par centre.
 */
class Vacation2026BesoinsController extends Controller
{
    public function __construct(private readonly VacationBesoinsService $besoins)
    {
    }

    // =====================================================================
    // Tableau de bord national
    // =====================================================================

    public function dashboard(Request $request)
    {
        $filters = $this->filters($request);
        $data = $this->besoins->compute();
        $lines = $this->besoins->lines($filters);

        $byLevel = $this->besoins->groupTotals($lines, 'level');
        $byExam = $this->besoins->groupTotals($lines, 'exam');
        $byPhase = $this->besoins->groupTotals($lines, 'phase');
        $matrix = [];
        foreach (Cat::LEVELS as $level => $label) {
            foreach (Cat::EXAMS as $exam) {
                $matrix[$level][$exam] = $this->besoins->totals($lines->where('level', $level)->where('exam', $exam));
            }
        }

        $drens = $this->besoins->byDren($lines);
        $ciscos = $this->besoins->byCisco($lines);
        $centres = $this->besoins->byCentre($lines);

        return view('vacation-2026.besoins.dashboard', [
            'filters' => $filters,
            'filterData' => $this->filterData(),
            'totals' => $this->besoins->totals($lines),
            'byLevel' => $byLevel,
            'byExam' => $byExam,
            'byPhase' => $byPhase,
            'matrix' => $matrix,
            'topDrens' => $drens->take(10),
            'drenCount' => $drens->count(),
            'ciscoCount' => $ciscos->count(),
            'centreCount' => $centres->count(),
            'stats' => $data['stats'],
            'warnings' => $data['warnings'],
            'levels' => Cat::LEVELS,
            'phases' => Cat::PHASES,
            'ratesDefined' => $data['rates_defined'],
        ]);
    }

    // =====================================================================
    // Personnes mobilisées par activité
    // =====================================================================

    public function activites(Request $request)
    {
        $filters = $this->filters($request);
        $data = $this->besoins->compute();
        $lines = $this->besoins->lines($filters);

        return view('vacation-2026.besoins.activites', [
            'filters' => $filters,
            'filterData' => $this->filterData(),
            'activities' => $this->besoins->byActivity($lines),
            'totals' => $this->besoins->totals($lines),
            'levels' => Cat::LEVELS,
            'phases' => Cat::PHASES,
            'warnings' => $data['warnings'],
        ]);
    }

    // =====================================================================
    // DREN / CISCO / centres
    // =====================================================================

    public function dren(Request $request)
    {
        $filters = $this->filters($request);
        $data = $this->besoins->compute();
        $lines = $this->besoins->lines($filters);
        $rows = $this->besoins->byDren($lines);

        return view('vacation-2026.besoins.scope-list', [
            'kind' => 'DREN',
            'title' => 'Besoins et montants par DREN',
            'rows' => $rows,
            'filters' => $filters,
            'filterData' => $this->filterData(),
            'totals' => $this->besoins->totals($lines),
            'levels' => Cat::LEVELS,
            'warnings' => $data['warnings'],
        ]);
    }

    public function cisco(Request $request)
    {
        $filters = $this->filters($request);
        $data = $this->besoins->compute();
        $lines = $this->besoins->lines($filters);

        return view('vacation-2026.besoins.scope-list', [
            'kind' => 'CISCO',
            'title' => 'Besoins et montants par CISCO',
            'rows' => $this->besoins->byCisco($lines),
            'filters' => $filters,
            'filterData' => $this->filterData(),
            'totals' => $this->besoins->totals($lines),
            'levels' => Cat::LEVELS,
            'warnings' => $data['warnings'],
        ]);
    }

    public function centres(Request $request)
    {
        $filters = $this->filters($request);
        $data = $this->besoins->compute();
        $lines = $this->besoins->lines($filters);
        $rows = $this->besoins->byCentre($lines);

        $type = (string) $request->query('type', '');
        if ($type !== '') {
            $rows = $rows->filter(fn ($r) => $r['type'] === $type)->values();
        }

        return view('vacation-2026.besoins.scope-list', [
            'kind' => 'CENTRE',
            'title' => "Besoins et montants par centre d'examen",
            'rows' => $rows,
            'filters' => $filters + ['type' => $type],
            'filterData' => $this->filterData() + ['centreTypes' => $this->besoins->compute()['lines']->pluck('centre_type')->filter()->unique()->sort()->values()],
            'totals' => $this->besoins->totals($lines->filter(fn ($l) => in_array($l['scope_kind'], ['CENTRE', 'EPS'], true))),
            'levels' => Cat::LEVELS,
            'warnings' => $data['warnings'],
        ]);
    }

    public function showDren(Request $request, Dren $dren)
    {
        $lines = $this->besoins->lines(['dren_id' => $dren->id] + $this->filters($request));

        return $this->detail($request, "DREN {$dren->nom}", $dren->province ? 'Province de '.$dren->province : null, $lines, [
            'back' => route('vacation2026.besoins.dren'),
            'ciscos' => $this->besoins->byCisco($lines),
            'centres' => $this->besoins->byCentre($lines),
        ]);
    }

    public function showCisco(Request $request, int $cisco)
    {
        $lines = $this->besoins->lines(['cisco_id' => $cisco] + $this->filters($request));
        $first = $lines->first();

        return $this->detail($request, 'CISCO '.($first['cisco_nom'] ?? "#{$cisco}"), $first ? 'DREN '.$first['dren_nom'] : null, $lines, [
            'back' => route('vacation2026.besoins.cisco'),
            'centres' => $this->besoins->byCentre($lines),
        ]);
    }

    public function showCentre(Request $request, string $key)
    {
        $lines = $this->besoins->lines(['scope_key' => $key] + $this->filters($request));
        $first = $lines->first();
        abort_if($first === null, 404);

        $subtitle = collect([$first['centre_type'], $first['parent_nom'] ? 'Sous-centre de '.$first['parent_nom'] : null,
            $first['cisco_nom'] ? 'CISCO '.$first['cisco_nom'] : null, $first['dren_nom'] ? 'DREN '.$first['dren_nom'] : null])
            ->filter()->implode(' · ');

        return $this->detail($request, $first['scope_nom'], $subtitle, $lines, [
            'back' => route('vacation2026.besoins.centres'),
        ]);
    }

    private function detail(Request $request, string $title, ?string $subtitle, Collection $lines, array $extra)
    {
        return view('vacation-2026.besoins.detail', $extra + [
            'title' => $title,
            'subtitle' => $subtitle,
            'filters' => $this->filters($request),
            'filterData' => $this->filterData(),
            'totals' => $this->besoins->totals($lines),
            'activities' => $this->besoins->byActivity($lines),
            'roles' => $this->besoins->byRole($lines),
            'byExam' => $this->besoins->groupTotals($lines, 'exam'),
            'byLevel' => $this->besoins->groupTotals($lines, 'level'),
            'levels' => Cat::LEVELS,
            'phases' => Cat::PHASES,
            'ciscos' => $extra['ciscos'] ?? collect(),
            'centres' => $extra['centres'] ?? collect(),
        ]);
    }

    // =====================================================================
    // Barèmes (taux de l'arrêté)
    // =====================================================================

    public function baremes()
    {
        $table = Vacation2026Bareme::table();
        $groups = collect(Cat::RATE_CODES)->map(fn ($def, $code) => $def + ['code' => $code])->groupBy('group');

        return view('vacation-2026.besoins.baremes', [
            'groups' => $groups,
            'table' => $table,
            'defaults' => $table[Vacation2026Bareme::DEFAULT_CODE] ?? [],
            'exams' => Cat::EXAMS,
        ]);
    }

    public function updateBaremes(Request $request)
    {
        $request->validate([
            'rates' => ['array'],
            'rates.*.*' => ['nullable', 'numeric', 'min:0', 'max:1000000000'],
            'modes' => ['array'],
            'modes.*' => ['in:JOUR,FORFAIT'],
            'default' => ['array'],
            'default.*' => ['nullable', 'numeric', 'min:0', 'max:1000000000'],
            'default_mode' => ['nullable', 'in:JOUR,FORFAIT'],
        ]);

        $codes = array_keys(Cat::RATE_CODES);

        // Taux par défaut (rôles sans taux propre)
        foreach (Cat::EXAMS as $exam) {
            $value = $request->input("default.$exam");
            $this->saveRate(Vacation2026Bareme::DEFAULT_CODE, $exam, $request->input('default_mode', 'JOUR'), $value);
        }

        // Taux par rôle
        foreach ($codes as $code) {
            $field = str_replace('.', '__', $code); // les points d'un nom de champ sont des séparateurs pour Laravel
            $mode = $request->input("modes.$field", Cat::RATE_CODES[$code]['mode']);
            foreach (Cat::EXAMS as $exam) {
                $this->saveRate($code, $exam, $mode, $request->input("rates.$field.$exam"));
            }
        }

        return redirect()->route('vacation2026.besoins.baremes')->with('success', 'Barèmes enregistrés.');
    }

    private function saveRate(string $code, string $exam, string $mode, mixed $value): void
    {
        if ($value === null || $value === '') {
            Vacation2026Bareme::query()->where('code', $code)->where('examen', $exam)->delete();

            return;
        }
        Vacation2026Bareme::query()->updateOrCreate(
            ['code' => $code, 'examen' => $exam],
            ['mode' => $mode, 'montant' => (float) $value]
        );
    }

    // =====================================================================
    // Paramètres
    // =====================================================================

    public function parametres()
    {
        return view('vacation-2026.besoins.parametres', [
            'params' => Vacation2026Parametre::values(),
            'drens' => Dren::query()->orderBy('nom')->get(),
            'provinces' => MadagascarProvinces::PROVINCES,
            'transcriptionChoices' => Vacation2026Parametre::TRANSCRIPTION_TYPES_ALL,
        ]);
    }

    public function updateParametres(Request $request)
    {
        $data = $request->validate([
            'annee' => ['required', 'digits:4'],
            'cepe_enveloppes_actif' => ['nullable', 'boolean'],
            'cepe_enveloppes_matieres' => ['required', 'integer', 'min:1', 'max:20'],
            'bepc_jours_ecrit_defaut' => ['required', 'integer', 'in:3,4'],
            'bepc_anglais_pct' => ['required', 'integer', 'min:0', 'max:100'],
            'transcription_types' => ['nullable', 'array'],
            'transcription_types.*' => ['string'],
            'inclure_speciale' => ['nullable', 'boolean'],
            'entites_sans_centre' => ['nullable', 'boolean'],
            'nb_provinces' => ['required', 'integer', 'min:0', 'max:30'],
            'eps_centres_par_cisco' => ['required', 'integer', 'min:1', 'max:20'],
            'drens' => ['nullable', 'array'],
            'drens.*.province' => ['nullable', 'string', 'max:60'],
            'drens.*.chef_lieu' => ['nullable', 'boolean'],
        ]);

        foreach (['cepe_enveloppes_actif', 'inclure_speciale', 'entites_sans_centre'] as $flag) {
            Vacation2026Parametre::put($flag, $request->boolean($flag) ? '1' : '0');
        }
        foreach (['annee', 'cepe_enveloppes_matieres', 'bepc_jours_ecrit_defaut', 'bepc_anglais_pct', 'nb_provinces', 'eps_centres_par_cisco'] as $key) {
            Vacation2026Parametre::put($key, (string) $data[$key]);
        }
        $allowed = Vacation2026Parametre::TRANSCRIPTION_TYPES_ALL;
        Vacation2026Parametre::put('transcription_types', json_encode(array_values(array_intersect($allowed, $data['transcription_types'] ?? []))));

        if ($request->has('drens')) {
            foreach (Dren::query()->get() as $dren) {
                $row = $request->input("drens.{$dren->id}", []);
                $dren->update([
                    'province' => ($row['province'] ?? '') !== '' ? $row['province'] : null,
                    'is_chef_lieu_province' => ! empty($row['chef_lieu']),
                ]);
            }
        }

        if ($request->boolean('deviner_provinces')) {
            foreach (Dren::query()->get() as $dren) {
                $guess = MadagascarProvinces::guess((string) $dren->nom);
                if ($guess !== null) {
                    $dren->update(['province' => $guess['province'], 'is_chef_lieu_province' => $guess['chef_lieu']]);
                }
            }
        }

        return redirect()->route('vacation2026.besoins.parametres')->with('success', 'Paramètres enregistrés.');
    }

    // =====================================================================
    // Export Excel
    // =====================================================================

    public function export(Request $request)
    {
        $filters = $this->filters($request);
        $lines = $this->besoins->lines($filters);
        $data = $this->besoins->compute();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Synthèse');

        $rows = [['Vacation 2026 — Besoins et montants'], [
            'Filtres', ($filters['exam'] ?: 'CEPE + BEPC').($filters['dren_id'] ? ' · DREN #'.$filters['dren_id'] : '').($filters['cisco_id'] ? ' · CISCO #'.$filters['cisco_id'] : ''),
        ], []];
        $t = $this->besoins->totals($lines);
        $rows[] = ['Montant total (Ar)', $t['amount']];
        $rows[] = ['Postes mobilisés (personnes × activités)', $t['persons']];
        $rows[] = ['Jours-agents', $t['person_days']];
        $rows[] = [];
        $rows[] = ['Niveau', 'Examen', 'Postes mobilisés', 'Jours-agents', 'Montant (Ar)'];
        foreach (Cat::LEVELS as $level => $label) {
            foreach (Cat::EXAMS as $exam) {
                $x = $this->besoins->totals($lines->where('level', $level)->where('exam', $exam));
                $rows[] = [$label, $exam, $x['persons'], $x['person_days'], $x['amount']];
            }
        }
        $rows[] = [];
        foreach ($data['warnings'] as $w) {
            $rows[] = ['Attention', $w];
        }
        $sheet->fromArray($rows, null, 'A1');
        $this->styleHeader($sheet, 'A7:E7');

        $this->addSheet($spreadsheet, 'Par activité',
            ['Examen', 'Niveau', 'Phase', 'Activité', 'Entités concernées', 'Personnes mobilisées', 'Moyenne / entité', 'Jours (min)', 'Jours (max)', 'Jours-agents', 'Montant (Ar)', 'Règle du décret'],
            $this->besoins->byActivity($lines)->map(fn ($a) => [
                $a['exam'], Cat::LEVELS[$a['level']] ?? $a['level'], Cat::PHASES[$a['phase']] ?? $a['phase'], $a['activity'],
                $a['units'], $a['persons'], $a['persons_per_unit'], $a['days_min'], $a['days_max'], $a['person_days'], $a['amount'], $a['rule'],
            ])->all());

        $this->addSheet($spreadsheet, 'Par DREN',
            ['DREN', 'Postes mobilisés', 'Jours-agents', 'Montant total (Ar)', 'dont DREN', 'dont CISCO', "dont centres d'écrit", 'dont centres de correction', 'dont transcription', 'dont EPS', 'CEPE (Ar)', 'BEPC (Ar)'],
            $this->besoins->byDren($lines)->map(fn ($r) => [
                $r['nom'], $r['persons'], $r['person_days'], $r['amount'],
                ...array_map(fn ($l) => (float) ($r['by_level'][$l]['amount'] ?? 0), ['DREN', 'CISCO', 'CENTRE_ECRIT', 'CENTRE_CORRECTION', 'TRANSCRIPTION', 'EPS']),
                (float) ($r['by_exam']['CEPE']['amount'] ?? 0), (float) ($r['by_exam']['BEPC']['amount'] ?? 0),
            ])->all());

        $this->addSheet($spreadsheet, 'Par CISCO',
            ['DREN', 'CISCO', 'Candidats', 'Centres', 'Postes mobilisés', 'Jours-agents', 'Montant total (Ar)', 'CEPE (Ar)', 'BEPC (Ar)'],
            $this->besoins->byCisco($lines)->map(fn ($r) => [
                $r['dren_nom'], $r['nom'], $r['candidates'], $r['centres'], $r['persons'], $r['person_days'], $r['amount'],
                (float) ($r['by_exam']['CEPE']['amount'] ?? 0), (float) ($r['by_exam']['BEPC']['amount'] ?? 0),
            ])->all());

        $this->addSheet($spreadsheet, 'Par centre',
            ['Centre', 'Type', 'Sous-centre de', 'DREN', 'CISCO', 'Examen(s)', 'Candidats', 'Postes mobilisés', 'Jours-agents', 'Montant (Ar)'],
            $this->besoins->byCentre($lines)->map(fn ($r) => [
                $r['nom'], $r['type'], $r['parent_nom'], $r['dren_nom'], $r['cisco_nom'], implode('+', $r['exams']),
                $r['candidates'], $r['persons'], $r['person_days'], $r['amount'],
            ])->all());

        $this->addSheet($spreadsheet, 'Détail par rôle',
            ['Examen', 'Niveau', 'Phase', 'Activité', 'Rôle', 'Périmètre', 'DREN', 'CISCO', 'Personnes', 'Jours', 'Taux (Ar)', 'Mode', 'Montant (Ar)'],
            $lines->map(fn ($l) => [
                $l['exam'], Cat::LEVELS[$l['level']] ?? $l['level'], Cat::PHASES[$l['phase']] ?? $l['phase'], $l['activity'], $l['role'],
                $l['scope_nom'], $l['dren_nom'], $l['cisco_nom'], $l['persons'], $l['days'], $l['rate'], $l['mode'], $l['amount'],
            ])->all());

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'vacation_2026_besoins_montants.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function addSheet(Spreadsheet $spreadsheet, string $title, array $header, array $rows): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle($title);
        $sheet->fromArray(array_merge([$header], $rows), null, 'A1');
        $this->styleHeader($sheet, 'A1:'.\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($header)).'1');
        $sheet->freezePane('A2');
        for ($i = 1; $i <= count($header); $i++) {
            $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }
    }

    private function styleHeader($sheet, string $range): void
    {
        $style = $sheet->getStyle($range);
        $style->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1E293B');
    }

    // =====================================================================
    // Filtres
    // =====================================================================

    /** @return array{exam:string, dren_id:?int, cisco_id:?int, level:string, phase:string} */
    private function filters(Request $request): array
    {
        $exam = strtoupper(trim((string) $request->query('exam', '')));
        $level = (string) $request->query('level', '');
        $phase = (string) $request->query('phase', '');

        return [
            'exam' => in_array($exam, Cat::EXAMS, true) ? $exam : '',
            'dren_id' => $request->query('dren_id') ? (int) $request->query('dren_id') : null,
            'cisco_id' => $request->query('cisco_id') ? (int) $request->query('cisco_id') : null,
            'level' => array_key_exists($level, Cat::LEVELS) ? $level : '',
            'phase' => array_key_exists($phase, Cat::PHASES) ? $phase : '',
        ];
    }

    private function filterData(): array
    {
        $topo = $this->besoins->compute()['topology'];

        return [
            'drens' => $topo['drens'],
            'ciscos' => $topo['ciscos'],
            'levels' => Cat::LEVELS,
            'phases' => Cat::PHASES,
            'exams' => Cat::EXAMS,
        ];
    }
}
