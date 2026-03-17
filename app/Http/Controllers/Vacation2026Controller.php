<?php

namespace App\Http\Controllers;

use App\Models\Vacation2026Activity;
use App\Models\Vacation2026Agent;
use App\Models\Vacation2026Assignment;
use App\Models\Vacation2026Setting;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Vacation2026Controller extends Controller
{
    private ?bool $hasActivityRateColumn = null;

    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $tab = (string) $request->query('tab', 'main');
        $filterExamen = trim((string) $request->query('filter_examen', ''));
        $filterActivity = $request->query('filter_activity');
        $balanceLocalite = trim((string) $request->query('balance_localite', ''));

        $activities = Vacation2026Activity::query()
            ->withCount('assignments')
            ->orderBy('examen')
            ->orderBy('ordre')
            ->orderBy('libelle')
            ->get();

        $agents = Vacation2026Agent::query()
            ->with(['assignments.activity'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('nom', 'like', "%{$search}%")
                        ->orWhere('im', 'like', "%{$search}%")
                        ->orWhere('localite_service', 'like', "%{$search}%")
                        ->orWhere('cin', 'like', "%{$search}%");
                });
            })
            ->orderBy('nom')
            ->paginate(20)
            ->withQueryString();

        $availableAgents = Vacation2026Agent::query()
            ->orderBy('nom')
            ->get(['id', 'nom', 'im', 'localite_service']);

        $assignmentsBase = Vacation2026Assignment::query()
            ->with(['agent', 'activity'])
            ->join('vacation_2026_activities', 'vacation_2026_activities.id', '=', 'vacation_2026_assignments.activity_id')
            ->join('vacation_2026_agents', 'vacation_2026_agents.id', '=', 'vacation_2026_assignments.agent_id')
            ->orderBy('vacation_2026_activities.examen')
            ->orderBy('vacation_2026_activities.ordre')
            ->orderBy('vacation_2026_activities.libelle')
            ->orderBy('vacation_2026_agents.nom')
            ->select('vacation_2026_assignments.*');
        $assignments = (clone $assignmentsBase)
            ->when($filterActivity !== null && $filterActivity !== '', function ($query) use ($filterActivity) {
                $query->where('vacation_2026_assignments.activity_id', (int) $filterActivity);
            })
            ->when($filterExamen !== '', function ($query) use ($filterExamen) {
                $query->where('vacation_2026_activities.examen', $filterExamen);
            })
            ->get();
        $assignmentsAll = (clone $assignmentsBase)->get();
        $assignmentRatesByActivity = Vacation2026Assignment::query()
            ->selectRaw('activity_id, MAX(taux) as taux')
            ->whereNotNull('taux')
            ->groupBy('activity_id')
            ->pluck('taux', 'activity_id');

        $setting = Vacation2026Setting::query()->first();
        $participantBalance = $assignmentsAll->map(function (Vacation2026Assignment $assignment) {
            $jours = (int) ($assignment->activity?->nb_jours ?? 0);
            $activityRate = $this->supportsActivityRate() && $assignment->activity?->taux_activite !== null
                ? (float) $assignment->activity->taux_activite
                : null;
            $fallbackRate = $assignment->taux !== null ? (float) $assignment->taux : null;
            $taux = $activityRate ?? $fallbackRate ?? 0.0;
            $montant = $jours * $taux;

            return [
                'assignment_id' => $assignment->id,
                'agent_id' => (int) ($assignment->agent?->id ?? 0),
                'activity_id' => (int) ($assignment->activity?->id ?? 0),
                'examen' => (string) ($assignment->activity?->examen ?? ''),
                'activite' => (string) ($assignment->activity?->libelle ?? ''),
                'nom' => (string) ($assignment->agent?->nom ?? ''),
                'im' => (string) ($assignment->agent?->im ?? ''),
                'localite' => (string) ($assignment->agent?->localite_service ?? ''),
                'jours' => $jours,
                'taux' => $taux,
                'montant' => $montant,
            ];
        });

        $activityBalance = $participantBalance
            ->groupBy('activity_id')
            ->map(function (Collection $rows) {
                $first = $rows->first();
                $count = $rows->count();
                $totalMontant = (float) $rows->sum('montant');
                $averageMontant = $count > 0 ? $totalMontant / $count : 0.0;
                $averageTaux = $count > 0 ? (float) $rows->avg('taux') : 0.0;

                return [
                    'activity_id' => (int) $first['activity_id'],
                    'examen' => (string) $first['examen'],
                    'activite' => (string) $first['activite'],
                    'participants' => $count,
                    'jours' => (int) $first['jours'],
                    'total_montant' => $totalMontant,
                    'average_montant' => $averageMontant,
                    'average_taux' => $averageTaux,
                ];
            })
            ->values();

        $averageByActivity = $activityBalance->keyBy('activity_id')->map(fn (array $row) => (float) $row['average_montant']);
        $participantBalance = $participantBalance->map(function (array $row) use ($averageByActivity) {
            $avg = (float) ($averageByActivity->get($row['activity_id']) ?? 0);
            $row['average_activity_montant'] = $avg;
            $row['ecart_montant'] = $row['montant'] - $avg;

            return $row;
        });

        $participantTotals = $participantBalance
            ->groupBy('agent_id')
            ->map(function (Collection $rows) use ($averageByActivity) {
                $first = $rows->first();
                $totalMontant = (float) $rows->sum('montant');
                $totalJours = (int) $rows->sum('jours');
                $expectedMontant = (float) $rows->sum(function (array $row) use ($averageByActivity) {
                    return (float) ($averageByActivity->get($row['activity_id']) ?? 0);
                });
                $equilibre = $totalMontant - $expectedMontant;
                $equilibreLabel = $equilibre > 0
                    ? 'trop percu'
                    : ($equilibre < 0 ? 'need more activities' : 'equilibre');
                $activities = $rows
                    ->map(fn (array $row) => trim($row['examen'].' - '.$row['activite']))
                    ->unique()
                    ->values()
                    ->implode(', ');

                return [
                    'agent_id' => (int) $first['agent_id'],
                    'nom' => (string) $first['nom'],
                    'im' => (string) $first['im'],
                    'localite' => (string) $first['localite'],
                    'activities' => $activities,
                    'assignments' => $rows->count(),
                    'jours' => $totalJours,
                    'montant' => $totalMontant,
                    'equilibre' => $equilibre,
                    'equilibre_label' => $equilibreLabel,
                ];
            })
            ->when($balanceLocalite !== '', function (Collection $rows) use ($balanceLocalite) {
                $needle = mb_strtolower($balanceLocalite);

                return $rows->filter(function (array $row) use ($needle) {
                    return str_contains(mb_strtolower($row['localite']), $needle);
                });
            })
            ->values();

        $serviceBalance = $participantBalance
            ->groupBy(fn (array $row) => $row['localite'] !== '' ? $row['localite'] : 'Non renseigné')
            ->map(function (Collection $rows, string $service) {
                $uniqueParticipants = $rows->pluck('agent_id')->unique()->count();
                $totalMontant = (float) $rows->sum('montant');

                return [
                    'service' => $service,
                    'participants' => $uniqueParticipants,
                    'assignments' => $rows->count(),
                    'montant' => $totalMontant,
                ];
            })
            ->when($balanceLocalite !== '', function (Collection $rows) use ($balanceLocalite) {
                $needle = mb_strtolower($balanceLocalite);

                return $rows->filter(function (array $row) use ($needle) {
                    return str_contains(mb_strtolower($row['service']), $needle);
                });
            })
            ->sortByDesc('montant')
            ->values();

        return view('repartition.vacation-2026', [
            'tab' => $tab,
            'filterExamen' => $filterExamen,
            'filterActivity' => $filterActivity,
            'balanceLocalite' => $balanceLocalite,
            'activities' => $activities,
            'agents' => $agents,
            'availableAgents' => $availableAgents,
            'assignments' => $assignments,
            'assignmentRatesByActivity' => $assignmentRatesByActivity,
            'activityBalance' => $activityBalance,
            'participantBalance' => $participantBalance,
            'participantTotals' => $participantTotals,
            'serviceBalance' => $serviceBalance,
            'setting' => $setting,
            'stats' => [
                'agents_total' => Vacation2026Agent::count(),
                'agents_affectes' => Vacation2026Agent::has('assignments')->count(),
                'agents_disponibles' => Vacation2026Agent::doesntHave('assignments')->count(),
            ],
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'activity_id' => ['required', 'exists:vacation_2026_activities,id'],
        ]);

        $activity = Vacation2026Activity::query()->withCount('assignments')->findOrFail((int) $payload['activity_id']);
        $activityRate = $this->supportsActivityRate() && $activity->taux_activite !== null
            ? (float) $activity->taux_activite
            : null;
        if ($activityRate === null) {
            $existingRate = Vacation2026Assignment::query()
                ->where('activity_id', $activity->id)
                ->whereNotNull('taux')
                ->value('taux');
            if ($existingRate !== null) {
                $activityRate = (float) $existingRate;
            }
        }
        $rows = $this->parseSpreadsheetRows($request, 'agents_file');

        $created = 0;
        $updated = 0;
        $assigned = 0;
        $errors = 0;
        $rejects = [];
        $currentActivityCount = (int) $activity->assignments_count;

        foreach ($rows as $index => $line) {
            $lineNumber = $index + 2;
            $nom = trim((string) ($line['nom'] ?? ''));
            $im = trim((string) ($line['im'] ?? ''));
            $localite = trim((string) ($line['localite_service'] ?? ''));
            $cin = trim((string) ($line['cin'] ?? ''));
            if ($nom === '' || $localite === '') {
                $errors++;
                $rejects[] = "Ligne {$lineNumber}: agent rejeté (nom et localité de service obligatoires).";
                continue;
            }
            if ($im === '') {
                $errors++;
                $rejects[] = "Ligne {$lineNumber}: agent {$nom} rejeté (IM obligatoire pour contrôle de correspondance).";
                continue;
            }

            $agent = null;
            $agentByIm = Vacation2026Agent::query()->where('im', $im)->first();
            if ($agentByIm) {
                $sameNom = $this->normalizeText($agentByIm->nom) === $this->normalizeText($nom);
                $sameLocalite = $this->normalizeText($agentByIm->localite_service) === $this->normalizeText($localite);
                if (! $sameNom || ! $sameLocalite) {
                    $errors++;
                    $rejects[] = "Ligne {$lineNumber}: IM {$im} rejeté (ne correspond pas au nom/localité existants en base).";
                    continue;
                }
                $agent = $agentByIm;
            }

            if (! $agent) {
                $agentByIdentity = Vacation2026Agent::query()
                    ->whereRaw('LOWER(nom) = ?', [mb_strtolower($nom)])
                    ->whereRaw('LOWER(localite_service) = ?', [mb_strtolower($localite)])
                    ->first();
                if ($agentByIdentity && $agentByIdentity->im !== null && trim((string) $agentByIdentity->im) !== $im) {
                    $errors++;
                    $rejects[] = "Ligne {$lineNumber}: {$nom} rejeté (nom/localité déjà liés à un autre IM: {$agentByIdentity->im}).";
                    continue;
                }
                $agent = $agentByIdentity;
            }

            if ($agent) {
                $agent->update([
                    'nom' => $nom,
                    'im' => $im !== '' ? $im : $agent->im,
                    'localite_service' => $localite,
                    'cin' => $cin !== '' ? $cin : null,
                ]);
                $updated++;
            } else {
                $agent = Vacation2026Agent::query()->create([
                    'nom' => $nom,
                    'im' => $im !== '' ? $im : null,
                    'localite_service' => $localite,
                    'cin' => $cin !== '' ? $cin : null,
                ]);
                $created++;
            }

            $existingAssignment = Vacation2026Assignment::query()
                ->where('agent_id', $agent->id)
                ->where('activity_id', $activity->id)
                ->first();
            if ($existingAssignment) {
                if ($activityRate !== null) {
                    $existingAssignment->update(['taux' => $activityRate]);
                }
                continue;
            }

            if ($currentActivityCount >= (int) $activity->max_agents) {
                $errors++;
                $rejects[] = "Ligne {$lineNumber}: {$nom} non affecté à {$activity->libelle} (quota {$activity->max_agents} atteint).";
                continue;
            }

            Vacation2026Assignment::query()->create([
                'agent_id' => $agent->id,
                'activity_id' => $activity->id,
                'taux' => $activityRate,
            ]);
            $currentActivityCount++;
            $assigned++;
        }

        $response = back()
            ->with('status', "Import activité {$activity->examen} - {$activity->libelle}: {$created} ajouté(s), {$updated} mis à jour, {$assigned} affecté(s), {$errors} rejeté(s).")
            ->with('import_rejects', array_slice($rejects, 0, 120));
        AuditLog::record($request, 'import_vacation_agents', [
            'activity_id' => $activity->id,
            'created' => $created,
            'updated' => $updated,
            'assigned' => $assigned,
            'errors' => $errors,
        ]);
        return $response;
    }

    public function updateSetting(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'entete' => ['nullable', 'string'],
            'considerant' => ['nullable', 'string'],
            'note_titre' => ['nullable', 'string', 'max:255'],
            'decision_titre' => ['nullable', 'string', 'max:255'],
            'presence_titre' => ['nullable', 'string', 'max:255'],
            'decompte_titre' => ['nullable', 'string', 'max:255'],
            'decision_reference' => ['nullable', 'string', 'max:255'],
            'signature' => ['nullable', 'string', 'max:255'],
        ]);

        $setting = Vacation2026Setting::query()->first();
        if (! $setting) {
            Vacation2026Setting::query()->create($payload);
        } else {
            $setting->update($payload);
        }

        return back()->with('status', 'Paramètres de document mis à jour.');
    }

    public function assign(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'agent_id' => ['required', 'exists:vacation_2026_agents,id'],
            'activity_id' => ['required', 'exists:vacation_2026_activities,id'],
            'taux' => ['nullable', 'numeric', 'min:0'],
        ]);

        $agent = Vacation2026Agent::query()->findOrFail((int) $payload['agent_id']);
        $alreadyAssigned = Vacation2026Assignment::query()
            ->where('agent_id', $agent->id)
            ->where('activity_id', (int) $payload['activity_id'])
            ->exists();
        if ($alreadyAssigned) {
            return back()->withErrors(['assign' => 'Cet agent est déjà affecté à cette activité.']);
        }

        $activity = Vacation2026Activity::query()->withCount('assignments')->findOrFail((int) $payload['activity_id']);
        if ($activity->assignments_count >= $activity->max_agents) {
            return back()->withErrors(['assign' => "Limite atteinte pour {$activity->libelle} ({$activity->max_agents} agents)."]);
        }

        Vacation2026Assignment::query()->create([
            'agent_id' => $agent->id,
            'activity_id' => $activity->id,
            'taux' => $payload['taux'] ?? null,
        ]);

        return back()->with('status', "Affectation enregistrée pour {$agent->nom}.");
    }

    public function updateActivity(Request $request, Vacation2026Activity $activity): RedirectResponse
    {
        $payload = $request->validate([
            'max_agents' => ['required', 'integer', 'min:1'],
            'nb_jours' => ['required', 'integer', 'min:1'],
            'taux_activite' => ['nullable', 'numeric', 'min:0'],
        ]);
        $rateInput = array_key_exists('taux_activite', $payload) && $payload['taux_activite'] !== null
            ? (float) $payload['taux_activite']
            : null;
        if (! $this->supportsActivityRate()) {
            unset($payload['taux_activite']);
        }

        if ($activity->assignments()->count() > (int) $payload['max_agents']) {
            return back()->withErrors([
                'max_agents' => "Impossible: {$activity->assignments()->count()} agent(s) déjà affecté(s).",
            ]);
        }

        $activity->update($payload);
        $updatedRateCount = 0;
        if ($rateInput !== null) {
            $updatedRateCount = Vacation2026Assignment::query()
                ->where('activity_id', $activity->id)
                ->update(['taux' => $rateInput]);
        }

        $status = "Paramètres mis à jour pour {$activity->libelle}.";
        if ($rateInput !== null) {
            $status .= " {$updatedRateCount} participant(s) mis à jour avec le nouveau taux.";
        }

        return back()->with('status', $status);
    }

    public function storeActivity(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'examen' => ['required', 'string', 'max:255'],
            'libelle' => ['required', 'string', 'max:255'],
            'max_agents' => ['required', 'integer', 'min:1'],
            'nb_jours' => ['required', 'integer', 'min:1'],
            'taux_activite' => ['nullable', 'numeric', 'min:0'],
            'ordre' => ['nullable', 'integer', 'min:0'],
        ]);
        if (! $this->supportsActivityRate()) {
            unset($payload['taux_activite']);
        }

        Vacation2026Activity::query()->create([
            'examen' => $payload['examen'],
            'libelle' => $payload['libelle'],
            'max_agents' => (int) $payload['max_agents'],
            'nb_jours' => (int) $payload['nb_jours'],
            'taux_activite' => $payload['taux_activite'] ?? null,
            'ordre' => (int) ($payload['ordre'] ?? 0),
        ]);

        return back()->with('status', 'Nouvelle activité ajoutée.');
    }

    public function removeAssignment(Vacation2026Assignment $assignment): RedirectResponse
    {
        $agentName = $assignment->agent?->nom ?? 'Agent';
        $assignment->delete();

        return back()->with('status', "Affectation supprimée pour {$agentName}.");
    }

    public function exportWord(Request $request, string $document)
    {
        $document = $this->normalizeDocumentType($document);
        if (! in_array($document, ['note-service', 'decision'], true)) {
            abort(404);
        }

        $activityId = $request->query('activity_id');
        $activity = null;
        if ($activityId !== null && $activityId !== '') {
            $activity = Vacation2026Activity::query()->find((int) $activityId);
        }

        $rows = $this->buildDocumentRows($activity?->id);
        $setting = Vacation2026Setting::query()->first();
        $filename = "vacation_2026_{$document}.doc";

        $decisionReference = $setting?->decision_reference;
        if ($document === 'decision' && $decisionReference === null) {
            $decisionReference = 'ELABORATION 150';
        }
        $headers = $this->documentHeaders($document);
        if ($document === 'decision' && $decisionReference !== null && $decisionReference !== '') {
            $headers[] = 'REFERENCE';
        }

        $content = view('repartition.vacation-2026-document', [
            'document' => $document,
            'rows' => $rows,
            'headers' => $headers,
            'documentTitle' => $this->resolveDocumentTitle($document, $setting),
            'decisionReference' => $decisionReference,
            'setting' => $setting,
            'selectedActivity' => $activity,
        ])->render();

        return response($content, 200, [
            'Content-Type' => 'application/msword; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function exportExcel(Request $request, string $document)
    {
        $document = $this->normalizeDocumentType($document);
        if (! in_array($document, ['decompte', 'presence', 'recap'], true)) {
            abort(404);
        }

        $activityId = $request->query('activity_id');
        $activity = null;
        if ($activityId !== null && $activityId !== '') {
            $activity = Vacation2026Activity::query()->find((int) $activityId);
        }
        $rows = $this->buildDocumentRows($activity?->id);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(strtoupper($document));

        if ($document === 'decompte') {
            $irsaPercent = (float) $request->query('irsa_percent', 0);
            if ($irsaPercent < 0) {
                $irsaPercent = 0;
            }
            $rowsPerPage = (int) $request->query('rows_per_page', 25);
            if ($rowsPerPage < 5) {
                $rowsPerPage = 5;
            }
            $withPageReports = (bool) $request->query('page_reports', false);

            $headers = ['EXAMEN', 'ACTIVITE', 'NOM', 'IM', 'LOCALITE DE SERVICE', 'NB JOURS', 'TAUX', 'MONTANT BRUT', 'IRSA %', 'MONTANT IRSA', 'MONTANT NET'];
            $sheet->fromArray($headers, null, 'A1');

            $line = 2;
            $pageCount = 0;
            $pageMontantBrut = 0.0;
            $pageMontantIrsa = 0.0;
            $pageMontantNet = 0.0;
            foreach ($rows as $item) {
                $brut = (float) ($item['montant'] ?? 0);
                $irsaAmount = $brut * ($irsaPercent / 100);
                $net = $brut - $irsaAmount;
                $sheet->fromArray([
                    $item['examen'],
                    $item['activite'],
                    $item['nom'],
                    $item['im'],
                    $item['localite'],
                    $item['jours'],
                    $item['taux'] !== null ? number_format((float) $item['taux'], 2, '.', '') : '',
                    number_format($brut, 2, '.', ''),
                    number_format($irsaPercent, 2, '.', ''),
                    number_format($irsaAmount, 2, '.', ''),
                    number_format($net, 2, '.', ''),
                ], null, "A{$line}");
                $line++;
                $pageCount++;
                $pageMontantBrut += $brut;
                $pageMontantIrsa += $irsaAmount;
                $pageMontantNet += $net;

                if ($withPageReports && $pageCount >= $rowsPerPage) {
                    $sheet->setCellValue("G{$line}", 'TOTAL PAGE');
                    $sheet->setCellValue("H{$line}", number_format($pageMontantBrut, 2, '.', ''));
                    $sheet->setCellValue("J{$line}", number_format($pageMontantIrsa, 2, '.', ''));
                    $sheet->setCellValue("K{$line}", number_format($pageMontantNet, 2, '.', ''));
                    $sheet->getStyle("G{$line}:K{$line}")->getFont()->setBold(true);
                    $line++;

                    $sheet->setBreak("A{$line}", \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::BREAK_ROW);
                    $sheet->setCellValue("G{$line}", 'REPORT PAGE PRECEDENTE');
                    $sheet->setCellValue("H{$line}", number_format($pageMontantBrut, 2, '.', ''));
                    $sheet->setCellValue("J{$line}", number_format($pageMontantIrsa, 2, '.', ''));
                    $sheet->setCellValue("K{$line}", number_format($pageMontantNet, 2, '.', ''));
                    $sheet->getStyle("G{$line}:K{$line}")->getFont()->setBold(true);
                    $line++;

                    $pageCount = 0;
                    $pageMontantBrut = 0.0;
                    $pageMontantIrsa = 0.0;
                    $pageMontantNet = 0.0;
                }
            }

            if ($withPageReports && $line > 2 && $pageCount > 0) {
                $sheet->setCellValue("G{$line}", 'TOTAL PAGE');
                $sheet->setCellValue("H{$line}", number_format($pageMontantBrut, 2, '.', ''));
                $sheet->setCellValue("J{$line}", number_format($pageMontantIrsa, 2, '.', ''));
                $sheet->setCellValue("K{$line}", number_format($pageMontantNet, 2, '.', ''));
                $sheet->getStyle("G{$line}:K{$line}")->getFont()->setBold(true);
                $line++;
            }

            if ($line === 2) {
                $sheet->setCellValue('A2', 'Aucune donnée affectée.');
            }
        } elseif ($document === 'presence') {
            $maxDays = (int) max(1, $rows->max('jours') ?? 1);
            $dayHeaders = [];
            for ($d = 1; $d <= $maxDays; $d++) {
                $dayHeaders[] = "J{$d}";
            }
            $headers = array_merge(['EXAMEN', 'ACTIVITE', 'NOM', 'IM', 'LOCALITE DE SERVICE'], $dayHeaders);
            $sheet->fromArray($headers, null, 'A1');

            $line = 2;
            foreach ($rows as $item) {
                $jours = (int) $item['jours'];
                $dayCells = [];
                for ($d = 1; $d <= $maxDays; $d++) {
                    $dayCells[] = $d <= $jours ? '' : '-';
                }

                $sheet->fromArray(array_merge([
                    $item['examen'],
                    $item['activite'],
                    $item['nom'],
                    $item['im'],
                    $item['localite'],
                ], $dayCells), null, "A{$line}");
                $line++;
            }

            if ($line === 2) {
                $sheet->setCellValue('A2', 'Aucune donnée affectée.');
            }

            $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        } else {
            $headers = ['EXAMEN', 'ACTIVITE', 'PARTICIPANTS', 'NB JOURS', 'TAUX MOYEN', 'MONTANT MOYEN', 'MONTANT TOTAL'];
            $sheet->fromArray($headers, null, 'A1');

            $line = 2;
            $activityBalance = $rows
                ->groupBy(fn (array $row) => $row['examen'].'|'.$row['activite'])
                ->map(function (Collection $items) {
                    $first = $items->first();
                    $count = $items->count();
                    $total = (float) $items->sum('montant');
                    $avgMontant = $count > 0 ? $total / $count : 0.0;
                    $avgTaux = $count > 0 ? (float) $items->avg('taux') : 0.0;

                    return [
                        'examen' => $first['examen'],
                        'activite' => $first['activite'],
                        'participants' => $count,
                        'jours' => (int) $first['jours'],
                        'avg_taux' => $avgTaux,
                        'avg_montant' => $avgMontant,
                        'total' => $total,
                    ];
                })
                ->values();

            foreach ($activityBalance as $item) {
                $sheet->fromArray([
                    $item['examen'],
                    $item['activite'],
                    $item['participants'],
                    $item['jours'],
                    number_format((float) $item['avg_taux'], 2, '.', ''),
                    number_format((float) $item['avg_montant'], 2, '.', ''),
                    number_format((float) $item['total'], 2, '.', ''),
                ], null, "A{$line}");
                $line++;
            }

            if ($line === 2) {
                $sheet->setCellValue('A2', 'Aucune donnée affectée.');
            }
        }

        $lastColumn = $sheet->getHighestColumn();
        $lastRow = max(2, $sheet->getHighestRow());
        $sheet->getStyle("A1:{$lastColumn}1")->getFont()->setBold(true);
        $sheet->getStyle("A1:{$lastColumn}1")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFDBEAFE');
        $sheet->getStyle("A1:{$lastColumn}{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $lastIndex = Coordinate::columnIndexFromString($lastColumn);
        for ($i = 1; $i <= $lastIndex; $i++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = "vacation_2026_{$document}.xlsx";

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function exportPdf(Request $request, string $document)
    {
        $document = $this->normalizeDocumentType($document);
        if (! in_array($document, ['note-service', 'decision', 'decompte', 'presence'], true)) {
            abort(404);
        }
        $activityId = $request->query('activity_id');
        $activity = null;
        if ($activityId !== null && $activityId !== '') {
            $activity = Vacation2026Activity::query()->find((int) $activityId);
        }
        $rows = $this->buildDocumentRows($activity?->id);
        $setting = Vacation2026Setting::query()->first();

        $decisionReference = $setting?->decision_reference;
        if ($document === 'decision' && $decisionReference === null) {
            $decisionReference = 'ELABORATION 150';
        }
        $headers = $this->documentHeaders($document);
        if ($document === 'decision' && $decisionReference !== null && $decisionReference !== '') {
            $headers[] = 'REFERENCE';
        }

        return response()->view('repartition.vacation-2026-document', [
            'document' => $document,
            'rows' => $rows,
            'headers' => $headers,
            'documentTitle' => $this->resolveDocumentTitle($document, $setting),
            'decisionReference' => $decisionReference,
            'setting' => $setting,
            'selectedActivity' => $activity,
        ]);
    }

    private function parseSpreadsheetRows(Request $request, string $field): array
    {
        $request->validate([
            $field => ['required', 'file', 'mimes:xlsx,xls,csv,txt'],
        ]);

        $file = $request->file($field);
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rawRows = $sheet->toArray(null, true, true, false);

        if (count($rawRows) < 2) {
            return [];
        }

        $headers = array_map(fn ($value) => $this->normalizeHeader((string) $value), $rawRows[0]);

        $parsed = [];
        for ($i = 1; $i < count($rawRows); $i++) {
            $line = $rawRows[$i];
            if (! is_array($line)) {
                continue;
            }

            $assoc = [];
            foreach ($headers as $index => $header) {
                if ($header === '') {
                    continue;
                }
                $assoc[$header] = trim((string) ($line[$index] ?? ''));
            }

            if ($assoc === []) {
                continue;
            }

            $parsed[] = [
                'nom' => $assoc['nom'] ?? '',
                'im' => $assoc['im'] ?? '',
                'localite_service' => $assoc['localite_service'] ?? ($assoc['localite'] ?? ''),
                'cin' => $assoc['cin'] ?? '',
            ];
        }

        return $parsed;
    }

    private function normalizeHeader(string $header): string
    {
        $header = mb_strtolower(trim($header));
        $header = str_replace(['é', 'è', 'ê', 'à', 'ù', 'î', 'ï', 'ô'], ['e', 'e', 'e', 'a', 'u', 'i', 'i', 'o'], $header);
        $header = preg_replace('/[^a-z0-9]+/', '_', $header) ?? $header;
        $header = trim($header, '_');

        return match ($header) {
            'nom_et_prenom', 'nom_prenoms', 'nom_prenom', 'agent' => 'nom',
            'indice_matricule', 'matricule', 'i_m' => 'im',
            'localite_de_service', 'localite_service', 'localite', 'service' => 'localite_service',
            default => $header,
        };
    }

    private function buildDocumentRows(?int $activityId = null): Collection
    {
        return Vacation2026Assignment::query()
            ->with(['agent', 'activity'])
            ->join('vacation_2026_activities', 'vacation_2026_activities.id', '=', 'vacation_2026_assignments.activity_id')
            ->join('vacation_2026_agents', 'vacation_2026_agents.id', '=', 'vacation_2026_assignments.agent_id')
            ->when($activityId !== null, fn ($query) => $query->where('vacation_2026_assignments.activity_id', $activityId))
            ->orderBy('vacation_2026_activities.examen')
            ->orderBy('vacation_2026_activities.ordre')
            ->orderBy('vacation_2026_activities.libelle')
            ->orderBy('vacation_2026_agents.nom')
            ->select('vacation_2026_assignments.*')
            ->get()
            ->map(function (Vacation2026Assignment $assignment) {
                $activityRate = $this->supportsActivityRate() && $assignment->activity?->taux_activite !== null
                    ? (float) $assignment->activity->taux_activite
                    : null;
                $fallbackRate = $assignment->taux !== null ? (float) $assignment->taux : null;
                $taux = $activityRate ?? $fallbackRate;
                $jours = (int) ($assignment->activity?->nb_jours ?? 0);

                return [
                    'examen' => (string) ($assignment->activity?->examen ?? ''),
                    'activite' => (string) ($assignment->activity?->libelle ?? ''),
                    'jours' => $jours,
                    'nom' => (string) ($assignment->agent?->nom ?? ''),
                    'im' => (string) ($assignment->agent?->im ?? ''),
                    'localite' => (string) ($assignment->agent?->localite_service ?? ''),
                    'cin' => (string) ($assignment->agent?->cin ?? ''),
                    'taux' => $taux,
                    'montant' => $taux !== null ? $taux * $jours : null,
                ];
            });
    }

    private function normalizeDocumentType(string $document): string
    {
        return match ($document) {
            'note-service', 'decompte', 'decision', 'presence', 'recap' => $document,
            default => 'note-service',
        };
    }

    private function documentHeaders(string $document): array
    {
        return match ($document) {
            'decompte' => ['EXAMEN', 'ACTIVITE', 'NOM', 'IM', 'LOCALITE DE SERVICE', 'NB JOURS', 'TAUX', 'MONTANT'],
            'presence' => ['EXAMEN', 'ACTIVITE', 'NOM', 'IM', 'LOCALITE DE SERVICE', 'CIN', 'SIGNATURE'],
            'decision' => ['N°', 'NOM ET PRENOMS', 'IM', 'LOCALITE DE SERVICE'],
            default => ['EXAMEN', 'ACTIVITE', 'NOM', 'IM', 'LOCALITE DE SERVICE'],
        };
    }

    private function documentLine(array $item, string $document): array
    {
        return match ($document) {
            'decompte' => [
                $item['examen'],
                $item['activite'],
                $item['nom'],
                $item['im'],
                $item['localite'],
                $item['jours'],
                $item['taux'] !== null ? number_format((float) $item['taux'], 2, '.', '') : '',
                $item['montant'] !== null ? number_format((float) $item['montant'], 2, '.', '') : '',
            ],
            'presence' => [
                $item['examen'],
                $item['activite'],
                $item['nom'],
                $item['im'],
                $item['localite'],
                $item['cin'],
                '',
            ],
            'decision' => [
                $item['examen'],
                $item['activite'],
                $item['nom'],
                $item['im'],
                $item['localite'],
                'ELABORATION 150',
            ],
            default => [
                $item['examen'],
                $item['activite'],
                $item['nom'],
                $item['im'],
                $item['localite'],
            ],
        };
    }

    private function supportsActivityRate(): bool
    {
        if ($this->hasActivityRateColumn === null) {
            $this->hasActivityRateColumn = Schema::hasColumn('vacation_2026_activities', 'taux_activite');
        }

        return $this->hasActivityRateColumn;
    }

    private function resolveDocumentTitle(string $document, ?Vacation2026Setting $setting): string
    {
        return match ($document) {
            'note-service' => $setting?->note_titre ?: 'NOTE DE SERVICE',
            'decision' => $setting?->decision_titre ?: 'DECISION',
            'presence' => $setting?->presence_titre ?: 'FICHE DE PRESENCE',
            'decompte' => $setting?->decompte_titre ?: 'ETAT DE DECOMPTE',
            default => strtoupper($document),
        };
    }

    private function normalizeText(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        $value = is_string($ascii) ? $ascii : $value;
        $value = preg_replace('/[^a-z0-9]+/i', ' ', $value) ?? $value;

        return trim($value);
    }
}
