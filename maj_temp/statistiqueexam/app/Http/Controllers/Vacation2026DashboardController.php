<?php

namespace App\Http\Controllers;

use App\Models\Dren;
use App\Models\Cisco;
use App\Services\Vacation2026DashboardService;
use App\Services\VacationDecreeService;
use App\Models\Vacation2026Activity;
use App\Models\Vacation2026Assignment;
use App\Models\Vacation2026EpsCentre;
use App\Models\RepartitionSalle;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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

    public function exportMenCentralExcel(Request $request)
    {
        $examFilter = strtoupper(trim((string) $request->query('exam', '')));
        if (! in_array($examFilter, ['', 'CEPE', 'BEPC'], true)) {
            $examFilter = '';
        }
        $phaseFilter = strtoupper(trim((string) $request->query('phase', '')));
        if (! in_array($phaseFilter, ['', 'AVANT_SESSION', 'PENDANT_SESSION', 'APRES_SESSION'], true)) {
            $phaseFilter = '';
        }
        $activityId = $request->query('activity_id') !== null && $request->query('activity_id') !== ''
            ? (int) $request->query('activity_id')
            : null;

        $central = $this->dashboardService->menCentralDashboard($examFilter, $phaseFilter, $activityId);
        $spreadsheet = new Spreadsheet();

        $centralSheet = $spreadsheet->getActiveSheet();
        $centralSheet->setTitle('MEN_CENTRAL');
        $centralSheet->fromArray([
            ['TABLEAU DE BORD MEN CENTRAL — VACATION 2026'],
            ['Examen', $examFilter !== '' ? $examFilter : 'Tous', 'Phase', $phaseFilter !== '' ? str_replace('_', ' ', $phaseFilter) : 'Toutes', 'Activité ID', $activityId ?? 'Toutes'],
            ['Indicateur', 'Valeur'],
            ['Activités CEPE prévues', (int) ($central['cepe']['planned'] ?? 0)],
            ['Activités BEPC prévues', (int) ($central['bepc']['planned'] ?? 0)],
            ['Personnel prévu', (int) ($central['total_planned'] ?? 0)],
            ['Personnel affecté', (int) ($central['total_assigned'] ?? 0)],
            ['Personnel restant', (int) ($central['remaining'] ?? 0)],
            ['Taux de réalisation (%)', (float) ($central['completion_percentage'] ?? 0)],
            ['Montant estimé (Ar)', (float) ($central['estimated_indemnity'] ?? 0)],
            [],
            ['EXAMEN', 'PHASE', 'ACTIVITÉ', 'PERSONNEL PRÉVU', 'AFFECTÉ', 'RESTANT', 'JOURS', 'TAUX (Ar)', 'MONTANT ESTIMÉ (Ar)'],
        ], null, 'A1');
        $line = 13;
        foreach ($central['activities_detail'] ?? [] as $activity) {
            $centralSheet->fromArray([[
                $activity['examen'] ?? '',
                $activity['phase'] ?? '',
                $activity['libelle'] ?? '',
                (int) ($activity['required'] ?? 0),
                (int) ($activity['assigned'] ?? 0),
                (int) ($activity['remaining'] ?? 0),
                (int) ($activity['days'] ?? 0),
                (float) ($activity['rate'] ?? 0),
                (float) ($activity['amount'] ?? 0),
            ]], null, "A{$line}");
            $line++;
        }

        $groupsSheet = $spreadsheet->createSheet();
        $groupsSheet->setTitle('GROUPES_CENTRAUX');
        $groupsSheet->fromArray([['EXAMEN', 'PHASE', 'ACTIVITÉ', 'GROUPE', 'PERSONNEL', 'JOURS', 'TAUX (Ar)', 'MONTANT (Ar)']], null, 'A1');
        $line = 2;
        foreach ($central['central_groups'] ?? [] as $group) {
            $groupsSheet->fromArray([[
                $group['examen'] ?? '',
                $group['phase'] ?? '',
                $group['activity'] ?? '',
                $group['group'] ?? '',
                (int) ($group['personnel'] ?? 0),
                (int) ($group['days'] ?? 0),
                (float) ($group['rate'] ?? 0),
                (float) ($group['amount'] ?? 0),
            ]], null, "A{$line}");
            $line++;
        }

        $activitiesSheet = $spreadsheet->createSheet();
        $activitiesSheet->setTitle('ACTIVITES_VACATION');
        $activitiesSheet->fromArray([[
            'ID', 'ANNÉE', 'EXAMEN', 'NIVEAU', 'PHASE', 'CODE ACTIVITÉ', 'ACTIVITÉ',
            'PERSONNEL PRÉVU', 'JOURS', 'TAUX (Ar)', 'RÈGLE', 'SOURCE DE LA RÈGLE',
        ]], null, 'A1');
        $line = 2;
        foreach (Vacation2026Activity::query()->where('year', '2026')->orderBy('level')->orderBy('examen')->orderBy('ordre')->get() as $activity) {
            $activitiesSheet->fromArray([[
                $activity->id,
                $activity->year,
                $activity->examen,
                $activity->level,
                $activity->phase,
                $activity->activity_code,
                $activity->libelle,
                (int) $activity->max_agents,
                (int) $activity->nb_jours,
                (float) ($activity->taux_activite ?? 0),
                $activity->rule_key,
                $activity->source_rule,
            ]], null, "A{$line}");
            $line++;
        }

        $candidateRows = RepartitionSalle::query()
            ->with('centreEcrit.centreCorrection.cisco.dren')
            ->where('annee', 'like', '2026%')
            ->get()
            ->groupBy('centre_ecrit_id')
            ->flatMap(function ($rows): array {
                $centre = $rows->first()?->centreEcrit;
                if (! $centre) {
                    return [];
                }
                $result = [];
                $cepe = (int) $rows->where('langue', 'TOTAL')->sum('effectif');
                if ($cepe > 0) {
                    $result[] = [
                        'dren' => $centre->centreCorrection?->cisco?->dren?->nom ?? '',
                        'cisco' => $centre->centreCorrection?->cisco?->nom ?? '',
                        'correction' => $centre->centreCorrection?->nom ?? '',
                        'centre' => $centre->nom,
                        'centre_id' => $centre->id,
                        'correction_id' => $centre->centre_correction_id,
                        'cisco_id' => $centre->centreCorrection?->cisco_id,
                        'dren_id' => $centre->centreCorrection?->cisco?->dren_id,
                        'exam' => 'CEPE',
                        'candidates' => $cepe,
                        'foreign' => 0,
                        'eps_inapt' => 0,
                    ];
                }
                $bepcRows = $rows->filter(fn ($row) => (string) $row->langue !== 'TOTAL');
                $hasTotal = $bepcRows->contains(fn ($row) => (string) $row->langue === RepartitionSalle::BEPC_TOTAL_KEY);
                $bepc = $hasTotal
                    ? (int) $bepcRows->where('langue', RepartitionSalle::BEPC_TOTAL_KEY)->sum('effectif')
                    : (int) $bepcRows->reject(fn ($row) => (string) $row->langue === 'Etranger (anglais obligatoire)')->sum('effectif');
                if ($bepc > 0) {
                    $result[] = [
                        'dren' => $centre->centreCorrection?->cisco?->dren?->nom ?? '',
                        'cisco' => $centre->centreCorrection?->cisco?->nom ?? '',
                        'correction' => $centre->centreCorrection?->nom ?? '',
                        'centre' => $centre->nom,
                        'centre_id' => $centre->id,
                        'correction_id' => $centre->centre_correction_id,
                        'cisco_id' => $centre->centreCorrection?->cisco_id,
                        'dren_id' => $centre->centreCorrection?->cisco?->dren_id,
                        'exam' => 'BEPC',
                        'candidates' => $bepc,
                        'foreign' => (int) $bepcRows->where('langue', 'Etranger (anglais obligatoire)')->sum('effectif'),
                        'eps_inapt' => (int) $bepcRows->sum('eps_inapt_effectif'),
                    ];
                }

                return $result;
            })->values();
        $candidateLookup = $candidateRows->groupBy(fn ($row) => $row['centre_id'].'|'.$row['exam']);
        $correctionCandidateLookup = $candidateRows->groupBy(fn ($row) => $row['correction_id'].'|'.$row['exam']);
        $ciscoCandidateLookup = $candidateRows->groupBy(fn ($row) => $row['cisco_id'].'|'.$row['exam']);
        $drenCandidateLookup = $candidateRows->groupBy(fn ($row) => $row['dren_id'].'|'.$row['exam']);
        $epsCandidateByCisco = Vacation2026EpsCentre::query()
            ->with('cisco')
            ->get()
            ->groupBy('cisco_id')
            ->map(fn ($rows) => (int) $rows->sum('candidate_count'));
        foreach (Cisco::query()->get() as $cisco) {
            $epsCandidateByCisco->put($cisco->id, (int) $epsCandidateByCisco->get($cisco->id, 0) + (int) ($cisco->manual_eps_candidates ?? 0));
        }

        $assignmentColumns = [
            'DREN', 'CISCO', 'CENTRE DE CORRECTION', 'CENTRE ÉCRIT', 'EXAMEN', 'CANDIDATS DU PÉRIMÈTRE', 'NIVEAU', 'PHASE',
            'ACTIVITÉ', 'AGENT', 'IM', 'CIN', 'RÔLE', 'STATUT', 'DATE DÉBUT', 'DATE FIN',
            'NB JOURS', 'TAUX (Ar)', 'PERSONNEL REQUIS', 'MONTANT (Ar)', 'NOTES',
        ];
        $assignments = Vacation2026Assignment::query()
            ->with(['dren', 'cisco', 'centreCorrection', 'centreEcrit', 'activity', 'agent'])
            ->orderBy('dren_id')->orderBy('cisco_id')->orderBy('activity_id')->orderBy('agent_id')
            ->get();
        $assignmentRows = $assignments->map(function (Vacation2026Assignment $assignment) use ($candidateLookup, $correctionCandidateLookup, $ciscoCandidateLookup, $drenCandidateLookup, $epsCandidateByCisco): array {
            $days = (int) ($assignment->nb_jours ?? $assignment->activity?->nb_jours ?? 0);
            $rate = (float) ($assignment->taux ?? $assignment->activity?->taux_activite ?? 0);
            $exam = strtoupper((string) ($assignment->activity?->examen ?? ''));
            $candidateKey = ($assignment->centre_ecrit_id ?? 0).'|'.$exam;
            $candidates = $assignment->centre_ecrit_id
                ? (int) $candidateLookup->get($candidateKey, collect())->sum('candidates')
                : ($assignment->centre_correction_id
                    ? (int) $correctionCandidateLookup->get($assignment->centre_correction_id.'|'.$exam, collect())->sum('candidates')
                    : ($assignment->cisco_id
                        ? (int) $ciscoCandidateLookup->get($assignment->cisco_id.'|'.$exam, collect())->sum('candidates')
                        : (int) $drenCandidateLookup->get($assignment->dren_id.'|'.$exam, collect())->sum('candidates')));
            if ($exam === 'EPS' || strtoupper((string) $assignment->level) === 'EPS') {
                $candidates = (int) $epsCandidateByCisco->get($assignment->cisco_id, 0);
            }

            return [
                $assignment->dren?->nom ?? '',
                $assignment->cisco?->nom ?? '',
                $assignment->centreCorrection?->nom ?? '',
                $assignment->centreEcrit?->nom ?? '',
                $exam,
                $candidates,
                $assignment->level,
                $assignment->phase ?? $assignment->activity?->phase ?? '',
                $assignment->activity?->libelle ?? '',
                $assignment->agent?->nom ?? '',
                $assignment->agent?->im ?? '',
                $assignment->agent?->cin ?? '',
                $assignment->role ?? '',
                $assignment->status ?? '',
                $assignment->start_date?->format('Y-m-d') ?? '',
                $assignment->end_date?->format('Y-m-d') ?? '',
                $days,
                $rate,
                (int) ($assignment->required_personnel ?? 0),
                $rate * $days,
                $assignment->notes ?? '',
            ];
        });

        $amountGroups = $assignments->groupBy(function (Vacation2026Assignment $assignment): string {
            return implode('|', [
                $assignment->dren_id ?? '', $assignment->cisco_id ?? '',
                $assignment->centre_correction_id ?? '', $assignment->centre_ecrit_id ?? '',
                $assignment->level ?? '', $assignment->activity_id ?? '',
            ]);
        });
        $amountRows = $amountGroups->map(function ($group): array {
            $assignment = $group->first();
            $days = (int) ($assignment->nb_jours ?? $assignment->activity?->nb_jours ?? 0);
            $rate = (float) ($assignment->taux ?? $assignment->activity?->taux_activite ?? 0);
            $ciscoId = $assignment->cisco_id ?? $assignment->centreCorrection?->cisco_id ?? $assignment->centreEcrit?->centreCorrection?->cisco_id;
            $drenId = $assignment->dren_id ?? $assignment->cisco?->dren_id ?? $assignment->centreCorrection?->cisco?->dren_id ?? $assignment->centreEcrit?->centreCorrection?->cisco?->dren_id;
            $correctionId = $assignment->centre_correction_id ?? $assignment->centreEcrit?->centre_correction_id;

            return [
                'dren_id' => $drenId,
                'cisco_id' => $ciscoId,
                'correction_id' => $correctionId,
                'ecrit_id' => $assignment->centre_ecrit_id,
                'dren' => $assignment->dren?->nom ?? $assignment->cisco?->dren?->nom ?? $assignment->centreCorrection?->cisco?->dren?->nom ?? $assignment->centreEcrit?->centreCorrection?->cisco?->dren?->nom ?? '',
                'cisco' => $assignment->cisco?->nom ?? $assignment->centreCorrection?->cisco?->nom ?? $assignment->centreEcrit?->centreCorrection?->cisco?->nom ?? '',
                'correction' => $assignment->centreCorrection?->nom ?? $assignment->centreEcrit?->centreCorrection?->nom ?? '',
                'ecrit' => $assignment->centreEcrit?->nom ?? '',
                'exam' => $assignment->activity?->examen ?? '',
                'level' => $assignment->level ?? '',
                'phase' => $assignment->phase ?? $assignment->activity?->phase ?? '',
                'activity' => $assignment->activity?->libelle ?? '',
                'agents' => $group->unique('agent_id')->count(),
                'days' => $days,
                'rate' => $rate,
                'amount' => (float) $group->sum(function (Vacation2026Assignment $row): float {
                    $rowDays = (int) ($row->nb_jours ?? $row->activity?->nb_jours ?? 0);
                    $rowRate = (float) ($row->taux ?? $row->activity?->taux_activite ?? 0);

                    return $rowDays * $rowRate;
                }),
            ];
        })->values();

        $allAssignmentsSheet = $spreadsheet->createSheet();
        $allAssignmentsSheet->setTitle('AFFECTATIONS');
        $allAssignmentsSheet->fromArray([$assignmentColumns], null, 'A1');
        $line = 2;
        foreach ($assignmentRows as $row) {
            $allAssignmentsSheet->fromArray([$row], null, "A{$line}");
            $line++;
        }
        if ($line === 2) {
            $allAssignmentsSheet->setCellValue('A2', 'Aucune affectation enregistrée.');
        }
        $allAssignmentsSheet->setAutoFilter('A1:U'.max(1, $line - 1));

        $drens = Dren::query()
            ->with(['ciscos' => fn ($query) => $query->withCount('centresCorrection')->with('vacation2026EpsCentres')])
            ->orderBy('nom')
            ->get();
        $centreVacationRows = collect($this->dashboardService->centreDashboard(null, null, $examFilter, $phaseFilter, $activityId));
        $usedSheetNames = ['MEN_CENTRAL', 'GROUPES_CENTRAUX', 'ACTIVITES_VACATION', 'AFFECTATIONS', 'CENTRES_EPS', 'CANDIDATS_CENTRES', 'MONTANTS_PAR_NIVEAU', 'MONTANTS_CENTRES', 'RECAP_TOTALE'];
        foreach ($drens as $dren) {
            $baseName = preg_replace('/[\\\\\/?*\[\]:]/u', '-', trim((string) $dren->nom)) ?: 'DREN';
            $baseName = mb_substr($baseName, 0, 31);
            $sheetName = $baseName;
            $suffix = 2;
            while (in_array(mb_strtolower($sheetName), array_map('mb_strtolower', $usedSheetNames), true)) {
                $tail = '-'.$suffix++;
                $sheetName = mb_substr($baseName, 0, 31 - mb_strlen($tail)).$tail;
            }
            $usedSheetNames[] = $sheetName;

            $drenSheet = $spreadsheet->createSheet();
            $drenSheet->setTitle($sheetName);
            $drenSheet->fromArray([
                ['DREN — '.$dren->nom.' — VACATION 2026'],
                [],
                ['CISCO', 'CENTRES DE CORRECTION', 'CANDIDATS EPS MANUELS', 'CENTRES EPS CONFIGURÉS', 'CANDIDATS EPS CONFIGURÉS', 'AGENTS AFFECTÉS'],
            ], null, 'A1');
            $line = 4;
            foreach ($dren->ciscos as $cisco) {
                $ciscoAssignments = $assignments->where('cisco_id', $cisco->id);
                $drenSheet->fromArray([[
                    $cisco->nom,
                    (int) ($cisco->centres_correction_count ?? 0),
                    (int) ($cisco->manual_eps_candidates ?? 0),
                    $cisco->vacation2026EpsCentres->count(),
                    (int) $cisco->vacation2026EpsCentres->sum('candidate_count'),
                    $ciscoAssignments->unique('agent_id')->count(),
                ]], null, "A{$line}");
                $line++;
            }

            $line += 2;
            $drenSheet->fromArray([['EFFECTIFS PAR CENTRE — UTILISABLES POUR LE FILTRE EXAMEN']], null, "A{$line}");
            $line++;
            $drenSheet->fromArray([['DREN', 'CISCO', 'CENTRE DE CORRECTION', 'CENTRE ÉCRIT', 'EXAMEN', 'CANDIDATS', 'ÉTRANGERS (DÉTAIL)', 'INAPTES EPS']], null, "A{$line}");
            $line++;
            foreach ($candidateRows->where('dren', (string) $dren->nom) as $candidateRow) {
                $drenSheet->fromArray([[
                    $candidateRow['dren'], $candidateRow['cisco'], $candidateRow['correction'], $candidateRow['centre'],
                    $candidateRow['exam'], $candidateRow['candidates'], $candidateRow['foreign'], $candidateRow['eps_inapt'],
                ]], null, "A{$line}");
                $line++;
            }

            $line += 2;
            $drenSheet->fromArray([['MONTANTS PRÉVISIONNELS PAR CENTRE — SELON LES CANDIDATS SAISIS']], null, "A{$line}");
            $line++;
            $drenSheet->fromArray([['DREN', 'CISCO', 'CENTRE DE CORRECTION', 'CENTRE', 'EXAMEN', 'CANDIDATS', 'NIVEAU', 'PHASE', 'ACTIVITÉ', 'PERSONNEL PRÉVU', 'JOURS', 'TAUX (Ar)', 'MONTANT ESTIMÉ (Ar)']], null, "A{$line}");
            $line++;
            foreach ($centreVacationRows as $centreRow) {
                if ((int) ($centreRow['dren_id'] ?? 0) !== (int) $dren->id) {
                    continue;
                }
                foreach (collect($centreRow['activities'] ?? [])->whereIn('level', ['CENTRE', 'EPS']) as $activityRow) {
                    $drenSheet->fromArray([[
                        $centreRow['dren_name'] ?? '', $centreRow['cisco_name'] ?? '', $centreRow['parent_centre_name'] ?? '',
                        $centreRow['centre_name'] ?? '', $activityRow['examen'] ?? '', (int) ($activityRow['candidates'] ?? $centreRow['total_candidates'] ?? 0),
                        $activityRow['level'] ?? 'CENTRE', $activityRow['phase'] ?? '', $activityRow['libelle'] ?? '',
                        (int) ($activityRow['required'] ?? 0), (int) ($activityRow['days'] ?? 0), (float) ($activityRow['rate'] ?? 0),
                        (float) ($activityRow['amount'] ?? 0),
                    ]], null, "A{$line}");
                    $line++;
                }
            }

            $line += 2;
            $drenSheet->fromArray([['MONTANTS DES AFFECTATIONS PAR ACTIVITÉ ET NIVEAU (Ar)']], null, "A{$line}");
            $line++;
            $amountHeaderRow = $line;
            $drenSheet->fromArray([['DREN', 'CISCO', 'CENTRE DE CORRECTION', 'CENTRE ÉCRIT', 'EXAMEN', 'NIVEAU', 'PHASE', 'ACTIVITÉ', 'AGENTS', 'JOURS', 'TAUX (Ar)', 'MONTANT (Ar)']], null, "A{$line}");
            $line++;
            foreach ($amountRows->where('dren_id', $dren->id) as $amountRow) {
                $drenSheet->fromArray([[
                    $amountRow['dren'], $amountRow['cisco'], $amountRow['correction'], $amountRow['ecrit'], $amountRow['exam'],
                    $amountRow['level'], $amountRow['phase'], $amountRow['activity'], $amountRow['agents'], $amountRow['days'],
                    $amountRow['rate'], $amountRow['amount'],
                ]], null, "A{$line}");
                $line++;
            }
            $drenSheet->setAutoFilter("A{$amountHeaderRow}:L".max($amountHeaderRow, $line - 1));

            $line += 2;
            $drenSheet->fromArray([['DÉTAIL DES AFFECTATIONS DE LA DREN']], null, "A{$line}");
            $line++;
            $drenSheet->fromArray([$assignmentColumns], null, "A{$line}");
            $line++;
            $assignmentDetailStart = $line;
            foreach ($assignmentRows as $row) {
                if ((string) ($row[0] ?? '') !== (string) $dren->nom) {
                    continue;
                }
                $drenSheet->fromArray([$row], null, "A{$line}");
                $line++;
            }
            if ($line === $assignmentDetailStart) {
                $drenSheet->setCellValue("A{$line}", 'Aucune affectation pour cette DREN.');
            }
        }

        $epsSheet = $spreadsheet->createSheet();
        $epsSheet->setTitle('CENTRES_EPS');
        $epsSheet->fromArray([['DREN', 'CISCO', 'CENTRE EPS', 'NOMBRE DE CANDIDATS']], null, 'A1');
        $line = 2;
        foreach (Vacation2026EpsCentre::query()->with('cisco.dren')->orderBy('cisco_id')->orderBy('name')->get() as $epsCentre) {
            $epsSheet->fromArray([[
                $epsCentre->cisco?->dren?->nom ?? '',
                $epsCentre->cisco?->nom ?? '',
                $epsCentre->name,
                (int) $epsCentre->candidate_count,
            ]], null, "A{$line}");
            $line++;
        }

        $candidateSheet = $spreadsheet->createSheet();
        $candidateSheet->setTitle('CANDIDATS_CENTRES');
        $candidateSheet->fromArray([['DREN', 'CISCO', 'CENTRE DE CORRECTION', 'CENTRE ÉCRIT', 'EXAMEN', 'CANDIDATS', 'ÉTRANGERS (DÉTAIL)', 'INAPTES EPS']], null, 'A1');
        $line = 2;
        foreach ($candidateRows as $candidateRow) {
            $candidateSheet->fromArray([[
                $candidateRow['dren'], $candidateRow['cisco'], $candidateRow['correction'], $candidateRow['centre'],
                $candidateRow['exam'], $candidateRow['candidates'], $candidateRow['foreign'], $candidateRow['eps_inapt'],
            ]], null, "A{$line}");
            $line++;
        }
        $candidateSheet->setAutoFilter('A1:H'.max(1, $line - 1));

        $amountSheet = $spreadsheet->createSheet();
        $amountSheet->setTitle('MONTANTS_PAR_NIVEAU');
        $amountSheet->fromArray([[
            'DREN', 'CISCO', 'CENTRE DE CORRECTION', 'CENTRE ÉCRIT', 'EXAMEN', 'NIVEAU', 'PHASE', 'ACTIVITÉ',
            'AGENTS', 'JOURS', 'TAUX (Ar)', 'MONTANT AFFECTÉ (Ar)',
        ]], null, 'A1');
        $line = 2;
        foreach ($amountRows as $amountRow) {
            $amountSheet->fromArray([[
                $amountRow['dren'], $amountRow['cisco'], $amountRow['correction'], $amountRow['ecrit'], $amountRow['exam'],
                $amountRow['level'], $amountRow['phase'], $amountRow['activity'], $amountRow['agents'], $amountRow['days'],
                $amountRow['rate'], $amountRow['amount'],
            ]], null, "A{$line}");
            $line++;
        }
        $amountSheet->setAutoFilter('A1:L'.max(1, $line - 1));

        $centreAmountSheet = $spreadsheet->createSheet();
        $centreAmountSheet->setTitle('MONTANTS_CENTRES');
        $centreAmountSheet->fromArray([[
            'DREN', 'CISCO', 'CENTRE DE CORRECTION', 'CENTRE', 'EXAMEN', 'CANDIDATS', 'NIVEAU', 'PHASE', 'ACTIVITÉ',
            'PERSONNEL PRÉVU', 'JOURS', 'TAUX (Ar)', 'MONTANT ESTIMÉ (Ar)',
        ]], null, 'A1');
        $line = 2;
        foreach ($centreVacationRows as $centreRow) {
            foreach (collect($centreRow['activities'] ?? [])->whereIn('level', ['CENTRE', 'EPS']) as $activityRow) {
                $centreAmountSheet->fromArray([[
                    $centreRow['dren_name'] ?? '', $centreRow['cisco_name'] ?? '', $centreRow['parent_centre_name'] ?? '',
                    $centreRow['centre_name'] ?? '', $activityRow['examen'] ?? '', (int) ($activityRow['candidates'] ?? $centreRow['total_candidates'] ?? 0),
                    $activityRow['level'] ?? 'CENTRE', $activityRow['phase'] ?? '', $activityRow['libelle'] ?? '',
                    (int) ($activityRow['required'] ?? 0), (int) ($activityRow['days'] ?? 0), (float) ($activityRow['rate'] ?? 0),
                    (float) ($activityRow['amount'] ?? 0),
                ]], null, "A{$line}");
                $line++;
            }
        }
        $centreAmountSheet->setAutoFilter('A1:M'.max(1, $line - 1));

        $centreEstimateRows = $centreVacationRows->map(function (array $centreRow): array {
            $activities = collect($centreRow['activities'] ?? [])->whereIn('level', ['CENTRE', 'EPS']);

            return array_merge($centreRow, ['estimated_amount' => (float) $activities->sum('amount')]);
        });
        $recapSheet = $spreadsheet->createSheet();
        $recapSheet->setTitle('RECAP_TOTALE');
        $recapSheet->fromArray([['RÉCAPITULATIF DES MONTANTS DE VACATION 2026'], ['Les montants d’affectations regroupent les niveaux descendants. La dernière colonne donne en plus les montants prévisionnels des centres calculés à partir des candidats saisis.'], [], ['TYPE', 'NIVEAU', 'DREN', 'CISCO', 'CENTRE DE CORRECTION', 'CENTRE ÉCRIT', 'MONTANT PROPRE AFFECTÉ (Ar)', 'MONTANT AFFECTÉ AVEC DESCENDANTS (Ar)', 'MONTANT PRÉVISIONNEL CENTRES (Ar)']], null, 'A1');
        $line = 5;
        $centralAssignments = $amountRows->where('level', 'CENTRAL');
        $allCentreEstimate = (float) $centreEstimateRows->sum('estimated_amount');
        $recapSheet->fromArray([['MEN CENTRAL', 'CENTRAL', '', '', '', '', (float) $centralAssignments->sum('amount'), (float) $amountRows->sum('amount'), $allCentreEstimate]], null, "A{$line}");
        $line++;
        $estimatedCentral = (float) ($central['estimated_indemnity'] ?? 0);
        $recapSheet->fromArray([['BUDGET MEN CENTRAL ESTIMÉ', 'CENTRAL', '', '', '', '', $estimatedCentral, $estimatedCentral, 0]], null, "A{$line}");
        $line++;
        foreach ($drens as $dren) {
            $rows = $amountRows->where('dren_id', $dren->id);
            $drenCentreEstimate = (float) $centreEstimateRows->where('dren_id', $dren->id)->sum('estimated_amount');
            $direct = (float) $rows->where('level', 'DREN')->sum('amount');
            $inclusive = (float) $rows->sum('amount');
            $recapSheet->fromArray([['DREN', 'DREN', $dren->nom, '', '', '', $direct, $inclusive, $drenCentreEstimate]], null, "A{$line}");
            $line++;
            foreach ($dren->ciscos as $cisco) {
                $ciscoRows = $rows->where('cisco_id', $cisco->id);
                $ciscoCentreEstimateRows = $centreEstimateRows->where('cisco_id', $cisco->id);
                $ciscoOwn = (float) $ciscoRows->where('level', 'CISCO')->sum('amount');
                $ciscoInclusive = (float) $ciscoRows->sum('amount');
                $recapSheet->fromArray([['CISCO', 'CISCO', $dren->nom, $cisco->nom, '', '', $ciscoOwn, $ciscoInclusive, (float) $ciscoCentreEstimateRows->sum('estimated_amount')]], null, "A{$line}");
                $line++;
                $epsRows = $ciscoRows->where('level', 'EPS');
                if ($epsRows->isNotEmpty()) {
                    $epsAmount = (float) $epsRows->sum('amount');
                    $recapSheet->fromArray([['EPS', 'EPS', $dren->nom, $cisco->nom, '', '', $epsAmount, $epsAmount, 0]], null, "A{$line}");
                    $line++;
                }
                $correctionIds = $ciscoRows->pluck('correction_id')->filter()
                    ->merge($ciscoCentreEstimateRows->pluck('centre_id')->filter())
                    ->unique();
                foreach ($correctionIds as $correctionId) {
                    $correctionRows = $ciscoRows->where('correction_id', $correctionId);
                    $writtenCentres = $ciscoCentreEstimateRows->where('centre_id', $correctionId);
                    $centreName = (string) ($correctionRows->first()['correction'] ?? $writtenCentres->first()['parent_centre_name'] ?? '');
                    $correctionOwn = (float) $correctionRows->where('level', 'CENTRE')->whereNull('ecrit_id')->sum('amount');
                    $recapSheet->fromArray([['CENTRE CORRECTION', 'CENTRE', $dren->nom, $cisco->nom, $centreName, '', $correctionOwn, (float) $correctionRows->sum('amount'), (float) $writtenCentres->sum('estimated_amount')]], null, "A{$line}");
                    $line++;
                    foreach ($writtenCentres as $writtenCentre) {
                        $ecritRows = $writtenCentre['centre_ecrit_id']
                            ? $ciscoRows->where('ecrit_id', $writtenCentre['centre_ecrit_id'])
                            : collect();
                        $ecritOwn = (float) $ecritRows->where('level', 'CENTRE')->sum('amount');
                        $recapSheet->fromArray([['CENTRE ÉCRIT', 'CENTRE', $dren->nom, $cisco->nom, $centreName, $writtenCentre['centre_name'] ?? '', $ecritOwn, (float) $ecritRows->sum('amount'), (float) $writtenCentre['estimated_amount']]], null, "A{$line}");
                        $line++;
                    }
                }
            }
        }

        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $lastColumn = $sheet->getHighestColumn();
            $lastRow = max(1, $sheet->getHighestRow());
            $sheet->getStyle("A1:{$lastColumn}1")->getFont()->setBold(true);
            $sheet->getStyle("A1:{$lastColumn}1")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFDBEAFE');
            if ($lastRow > 2) {
                $sheet->getStyle("A3:{$lastColumn}{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            }
            $sheet->freezePane('A2');
            for ($column = 1; $column <= Coordinate::columnIndexFromString($lastColumn); $column++) {
                $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($column))->setAutoSize(true);
            }
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'vacation_2026_men_central_dren_cisco.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
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
            $selectedCisco = Cisco::with('dren')->find($ciscoId);
            return view('vacation-2026.dashboards.cisco-single', array_merge($data, [
                'allCiscos' => Cisco::with('dren')->get(['id', 'nom', 'dren_id', 'manual_eps_candidates']),
                'allCentres' => \App\Models\CentreCorrection::where('cisco_id', $data['cisco_id'])->get(['id', 'nom']),
                'selectedCiscoId' => $ciscoId,
                'selectedCentreId' => $centreId,
                'selectedCisco' => $selectedCisco,
                ...$this->filterViewData($filters, 'CISCO'),
            ]));
        }

        // Otherwise show list of all CISCOs
        return view('vacation-2026.dashboards.cisco-list', [
            'dashboards' => $dashboards,
            'selectedCiscoId' => $ciscoId,
            'allCiscos' => Cisco::with('dren')->get(['id', 'nom', 'dren_id', 'manual_eps_candidates']),
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
    $centreEcritId = $request->query('centre_ecrit_id');
    $filters = $this->dashboardFilters($request);

    $dashboards = $this->dashboardService->centreDashboard(
        $centreId ? (int) $centreId : null,
        null,
        $filters[0],
        $filters[1],
        $filters[2],
        $centreEcritId ? (int) $centreEcritId : null
    );

    // If a specific centre is requested
    if (($centreId || $centreEcritId) && !empty($dashboards)) {
        $data = $dashboards[0];

        return view('vacation-2026.dashboards.centre-single', array_merge($data, [
                'allCentres' => $this->dashboardService->centreDashboard(),
            'selectedCentreId' => $centreId,
            'selectedCentreEcritId' => $centreEcritId,
            ...$this->filterViewData($filters, 'CENTRE'),
        ]));
    }

    // Use a compact, searchable and paginated listing: a card per centre does
    // not remain readable when thousands of centres are imported.
    $ecritOnly = collect($dashboards)->filter(function ($dashboard) {
        return ($dashboard['centre_type'] ?? null) === VacationDecreeService::CENTRE_TYPE_ECRIT;
    });

    $correctionOnly = collect($dashboards)->filter(function ($dashboard) {
        return ($dashboard['centre_type'] ?? null) === VacationDecreeService::CENTRE_TYPE_CORRECTION;
    });

    $jumeles = collect($dashboards)->filter(function ($dashboard) {
        return ($dashboard['centre_type'] ?? null) === VacationDecreeService::CENTRE_TYPE_JUMELES;
    });

    $search = trim((string) $request->query('search', ''));
    $filtered = collect($dashboards)->filter(fn ($item) => $search === '' || str_contains(mb_strtolower($item['centre_name']), mb_strtolower($search)))->values();
    $perPage = 30;
    $page = LengthAwarePaginator::resolveCurrentPage();
    $paged = new LengthAwarePaginator($filtered->forPage($page, $perPage)->values(), $filtered->count(), $perPage, $page, ['path' => $request->url(), 'query' => $request->query()]);

    return view('vacation-2026.dashboards.centre-list', [
        'dashboards' => $paged,
        'totalCentres' => $filtered->count(),
        'search' => $search,
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
            // EPS is an integral part of the BEPC session, not a separate exam.
            'exams' => ['CEPE', 'BEPC'],
            'phases' => ['AVANT_SESSION', 'PENDANT_SESSION', 'APRES_SESSION', 'AVANT_EPREUVES_EPS', 'PENDANT_EPREUVES_EPS', 'APRES_EPREUVES_EPS'],
        ];
    }
}
