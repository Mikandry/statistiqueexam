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
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
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
        $filterLevel = trim((string) $request->query('filter_level', ''));
        $balanceLocalite = trim((string) $request->query('balance_localite', ''));

        $activities = Vacation2026Activity::query()
            ->withCount('assignments')
            ->when($filterLevel !== '', fn ($q) => $q->where('level', $filterLevel))
            ->when($filterExamen !== '', fn ($q) => $q->where('examen', $filterExamen))
            ->orderBy('level')
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
        $equilibreData = $this->buildEquilibreData($balanceLocalite);
        $participantEquilibre = $equilibreData['participant_equilibre'];

        return view('repartition.vacation-2026', [
            'tab' => $tab,
            'filterExamen' => $filterExamen,
            'filterActivity' => $filterActivity,
            'filterLevel' => $filterLevel,
            'balanceLocalite' => $balanceLocalite,
            'activities' => $activities,
            'agents' => $agents,
            'availableAgents' => $availableAgents,
            'assignments' => $assignments,
            'assignmentRatesByActivity' => $assignmentRatesByActivity,
            'participantEquilibre' => $participantEquilibre,
            'setting' => $setting,
            'availableLevels' => [
                '' => 'Tous les niveaux',
                'CENTRAL' => 'MEN Central',
                'DREN' => 'DREN',
                'CISCO' => 'CISCO',
                'CENTRE' => 'Centre d\'examen',
                'EPS' => 'EPS / GYM',
            ],
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
            'decision_article_1' => ['nullable', 'string'],
            'decision_article_2' => ['nullable', 'string'],
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
            'max_agents' => ['nullable', 'integer', 'min:0'],
            'nb_jours' => ['required', 'integer', 'min:1'],
            'taux_activite' => ['nullable', 'numeric', 'min:0'],
        ]);
        $rateInput = array_key_exists('taux_activite', $payload) && $payload['taux_activite'] !== null
            ? (float) $payload['taux_activite']
            : null;
        if (! $this->supportsActivityRate()) {
            unset($payload['taux_activite']);
        }

        if ($payload['max_agents'] !== null && $activity->assignments()->count() > (int) $payload['max_agents']) {
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

    public function updateActivityGroup(Request $request, \App\Models\Vacation2026ActivityGroup $group): RedirectResponse
    {
        abort_unless($group->activity?->level === 'CENTRAL', 404);
        $payload = $request->validate([
            'personnel' => ['required', 'integer', 'min:0'],
            'nb_jours' => ['required', 'integer', 'min:1'],
            'taux' => ['required', 'numeric', 'min:0'],
        ]);
        $group->update($payload);
        return back()->with('status', "Groupe {$group->groupe} mis à jour.");
    }

    public function updateEpsCapacity(Request $request, \App\Models\CentreCorrection $centre): RedirectResponse
    {
        $payload = $request->validate(['eps_capacity' => ['required', 'integer', 'min:1', 'max:2']]);
        $centre->update($payload);
        return back()->with('status', "Capacité EPS mise à jour pour {$centre->nom}.");
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

        $headers = $this->documentHeaders($document);

        $content = view('repartition.vacation-2026-document', [
            'document' => $document,
            'rows' => $rows,
            'headers' => $headers,
            'documentTitle' => $this->resolveDocumentTitle($document, $setting),
            'setting' => $setting,
            'selectedActivity' => $activity,
        ])->render();
        $content = "\xEF\xBB\xBF".$content;

        return response($content, 200, [
            'Content-Type' => 'application/msword; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Content-Transfer-Encoding' => 'binary',
        ]);
    }

    public function exportExcel(Request $request, string $document)
    {
        $document = $this->normalizeDocumentType($document);
        if (! in_array($document, ['decompte', 'presence', 'recap', 'equilibre'], true)) {
            abort(404);
        }

        $activityId = $request->query('activity_id');
        $activity = null;
        if ($activityId !== null && $activityId !== '') {
            $activity = Vacation2026Activity::query()->find((int) $activityId);
        }
        $rows = $this->buildDocumentRows($activity?->id);
        $setting = Vacation2026Setting::query()->first();
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
            $firstPageRows = (int) $request->query('first_page_rows', $rowsPerPage);
            if ($firstPageRows < 1) {
                $firstPageRows = 1;
            }
            if ($firstPageRows > $rowsPerPage) {
                $firstPageRows = $rowsPerPage;
            }
            $withPageReports = (bool) $request->query('page_reports', false);
            $withFirstPageHeader = (bool) $request->query('first_page_header', true);

            $headers = ['N°', 'ACTIVITE', 'NOM', 'IM', 'LOCALITE DE SERVICE', 'NB JOURS', 'TAUX', 'MONTANT BRUT', 'IRSA %', 'MONTANT IRSA', 'MONTANT NET'];
            $line = 1;
            if ($withFirstPageHeader) {
                $line = $this->writeWorksheetHeader(
                    $sheet,
                    'K',
                    $this->resolveDocumentTitle($document, $setting),
                    $setting,
                    $activity
                );
            }
            $headerRow = $line;
            $sheet->fromArray($headers, null, "A{$headerRow}");
            $sheet->getStyle("A{$headerRow}:K{$headerRow}")->getFont()->setBold(true);
            $sheet->getStyle("A{$headerRow}:K{$headerRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFDBEAFE');
            $line = $headerRow + 1;

            $pageIndex = 1;
            $pageCount = 0;
            $pageMontantBrut = 0.0;
            $pageMontantIrsa = 0.0;
            $pageMontantNet = 0.0;
            $cumulMontantBrut = 0.0;
            $cumulMontantIrsa = 0.0;
            $cumulMontantNet = 0.0;
            $currentPageLimit = $withFirstPageHeader ? $firstPageRows : $rowsPerPage;
            $totalRows = $rows->count();
            foreach ($rows as $index => $item) {
                $brut = (float) ($item['montant'] ?? 0);
                $irsaAmount = $brut * ($irsaPercent / 100);
                $net = $brut - $irsaAmount;
                $sheet->fromArray([
                    $index + 1,
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

                if ($withPageReports && $pageCount >= $currentPageLimit) {
                    $cumulMontantBrut += $pageMontantBrut;
                    $cumulMontantIrsa += $pageMontantIrsa;
                    $cumulMontantNet += $pageMontantNet;
                    $this->writeDecompteTotalsRow(
                        $sheet,
                        $line,
                        $pageIndex > 1 ? 'TOTAL AVEC REPORT' : 'TOTAL PAGE',
                        $cumulMontantBrut,
                        $cumulMontantIrsa,
                        $cumulMontantNet
                    );
                    $line++;

                    if ($index < ($totalRows - 1)) {
                        $sheet->setBreak("A{$line}", Worksheet::BREAK_ROW);
                        $this->writeDecompteTotalsRow(
                            $sheet,
                            $line,
                            'REPORT PAGE PRECEDENTE',
                            $cumulMontantBrut,
                            $cumulMontantIrsa,
                            $cumulMontantNet
                        );
                        $line++;
                    }

                    $pageIndex++;
                    $pageCount = 0;
                    $pageMontantBrut = 0.0;
                    $pageMontantIrsa = 0.0;
                    $pageMontantNet = 0.0;
                    $currentPageLimit = $rowsPerPage;
                }
            }

            if ($withPageReports && $pageCount > 0) {
                $cumulMontantBrut += $pageMontantBrut;
                $cumulMontantIrsa += $pageMontantIrsa;
                $cumulMontantNet += $pageMontantNet;
                $this->writeDecompteTotalsRow(
                    $sheet,
                    $line,
                    $pageIndex > 1 ? 'TOTAL AVEC REPORT' : 'TOTAL PAGE',
                    $cumulMontantBrut,
                    $cumulMontantIrsa,
                    $cumulMontantNet
                );
                $line++;
            }

            if ($line === ($headerRow + 1)) {
                $sheet->setCellValue("A{$line}", 'Aucune donnée affectée.');
            }
        } elseif ($document === 'presence') {
            $maxDays = (int) max(1, $rows->max('jours') ?? 1);
            $dayHeaders = [];
            for ($d = 1; $d <= $maxDays; $d++) {
                $dayHeaders[] = "J{$d}";
            }
            $headers = array_merge(['N°', 'ACTIVITE', 'NOM', 'IM', 'LOCALITE DE SERVICE'], $dayHeaders);
            $sheet->fromArray($headers, null, 'A1');
            $presenceLastColumn = Coordinate::stringFromColumnIndex(count($headers));
            $sheet->getStyle("A1:{$presenceLastColumn}1")->getFont()->setBold(true);
            $sheet->getStyle("A1:{$presenceLastColumn}1")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFDBEAFE');

            $line = 2;
            foreach ($rows as $index => $item) {
                $jours = (int) $item['jours'];
                $dayCells = [];
                for ($d = 1; $d <= $maxDays; $d++) {
                    $dayCells[] = $d <= $jours ? '' : '-';
                }

                $sheet->fromArray(array_merge([
                    $index + 1,
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
        } elseif ($document === 'recap') {
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
        } else {
            $equilibreData = $this->buildEquilibreData(trim((string) $request->query('balance_localite', '')));
            $summaryRows = $equilibreData['participant_equilibre'];
            $detailRows = $equilibreData['participant_rows'];

            $sheet->setTitle('SYNTHESE AGENTS');
            $headers = ['NOM', 'IM', 'LOCALITE / SERVICE', 'ACTIVITES', 'NB AFFECTATIONS', 'NB ACTIVITES', 'MONTANT TOTAL', 'ECART AFFECTATIONS LOCALITE', 'ECART MONTANT LOCALITE', 'ANOMALIE'];
            $sheet->fromArray($headers, null, 'A1');

            $line = 2;
            foreach ($summaryRows as $item) {
                $sheet->fromArray([
                    $item['nom'],
                    $item['im'],
                    $item['localite'],
                    $item['activities'],
                    $item['nb_affectations'],
                    $item['nb_activites'],
                    number_format((float) $item['montant_total'], 2, '.', ''),
                    number_format((float) $item['ecart_affectations_localite'], 2, '.', ''),
                    number_format((float) $item['ecart_montant_localite'], 2, '.', ''),
                    $item['anomalie'],
                ], null, "A{$line}");
                $line++;
            }

            if ($line === 2) {
                $sheet->setCellValue('A2', 'Aucune donnée d\'équilibrage.');
            }

            $detailSheet = $spreadsheet->createSheet();
            $detailSheet->setTitle('DETAIL AFFECTATIONS');
            $detailHeaders = ['NOM', 'IM', 'LOCALITE / SERVICE', 'EXAMEN', 'ACTIVITE', 'NB JOURS', 'TAUX', 'MONTANT', 'CLE AGENT', 'ANOMALIE'];
            $detailSheet->fromArray($detailHeaders, null, 'A1');

            $anomaliesByParticipant = $summaryRows
                ->mapWithKeys(fn (array $item) => [$item['participant_key'] => $item['anomalie']]);

            $line = 2;
            foreach ($detailRows as $item) {
                $detailSheet->fromArray([
                    $item['nom'],
                    $item['im'],
                    $item['localite'],
                    $item['examen'],
                    $item['activite'],
                    $item['jours'],
                    number_format((float) $item['taux'], 2, '.', ''),
                    number_format((float) $item['montant'], 2, '.', ''),
                    $item['participant_key'],
                    $anomaliesByParticipant->get($item['participant_key'], ''),
                ], null, "A{$line}");
                $line++;
            }

            if ($line === 2) {
                $detailSheet->setCellValue('A2', 'Aucune affectation.');
            }
        }

        foreach ($spreadsheet->getAllSheets() as $currentSheet) {
            $lastColumn = $currentSheet->getHighestColumn();
            $lastRow = max(2, $currentSheet->getHighestRow());
            $currentSheet->getStyle("A1:{$lastColumn}1")->getFont()->setBold(true);
            $currentSheet->getStyle("A1:{$lastColumn}1")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFDBEAFE');
            $currentSheet->getStyle("A1:{$lastColumn}{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            $lastIndex = Coordinate::columnIndexFromString($lastColumn);
            for ($i = 1; $i <= $lastIndex; $i++) {
                $currentSheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
            }
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

        $headers = $this->documentHeaders($document);

        return response()->view('repartition.vacation-2026-document', [
            'document' => $document,
            'rows' => $rows,
            'headers' => $headers,
            'documentTitle' => $this->resolveDocumentTitle($document, $setting),
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
            'note-service', 'decompte', 'decision', 'presence', 'recap', 'equilibre' => $document,
            default => 'note-service',
        };
    }

    private function buildEquilibreData(string $balanceLocalite = ''): array
    {
        $participantRows = $this->buildDocumentRows()
            ->map(function (array $row) {
                $im = trim((string) ($row['im'] ?? ''));
                $localite = trim((string) ($row['localite'] ?? ''));

                return [
                    'examen' => (string) ($row['examen'] ?? ''),
                    'activite' => (string) ($row['activite'] ?? ''),
                    'nom' => (string) ($row['nom'] ?? ''),
                    'im' => $im,
                    'localite' => $localite !== '' ? $localite : 'Non renseigné',
                    'jours' => (int) ($row['jours'] ?? 0),
                    'taux' => (float) ($row['taux'] ?? 0),
                    'montant' => (float) ($row['montant'] ?? 0),
                    'participant_key' => $im !== ''
                        ? 'im:'.$im
                        : 'identity:'.$this->normalizeText((string) ($row['nom'] ?? '')).'|'.$this->normalizeText($localite),
                ];
            });

        $agentsWithAssignments = Vacation2026Agent::query()
            ->has('assignments')
            ->get(['nom', 'im', 'localite_service']);

        $imAnomalies = $agentsWithAssignments
            ->filter(fn (Vacation2026Agent $agent) => trim((string) $agent->im) !== '')
            ->groupBy(fn (Vacation2026Agent $agent) => trim((string) $agent->im))
            ->map(function (Collection $agents) {
                return $agents->pluck('nom')
                    ->map(fn ($name) => $this->normalizeText((string) $name))
                    ->filter()
                    ->unique()
                    ->values();
            })
            ->filter(fn (Collection $names) => $names->count() > 1);

        $identityAnomalies = $agentsWithAssignments
            ->groupBy(fn (Vacation2026Agent $agent) => $this->normalizeText((string) $agent->nom).'|'.$this->normalizeText((string) $agent->localite_service))
            ->map(function (Collection $agents) {
                return $agents->pluck('im')
                    ->map(fn ($im) => trim((string) $im))
                    ->filter()
                    ->unique()
                    ->values();
            })
            ->filter(fn (Collection $ims) => $ims->count() > 1);

        $participantEquilibre = $participantRows
            ->groupBy('participant_key')
            ->map(function (Collection $rows) use ($imAnomalies, $identityAnomalies) {
                $first = $rows->first();
                $activities = $rows
                    ->map(fn (array $row) => trim($row['examen'].' - '.$row['activite']))
                    ->filter()
                    ->unique()
                    ->values();
                $im = trim((string) ($first['im'] ?? ''));
                $identityKey = $this->normalizeText((string) ($first['nom'] ?? '')).'|'.$this->normalizeText((string) ($first['localite'] ?? ''));
                $anomalies = collect();

                if ($im !== '' && $imAnomalies->has($im)) {
                    $anomalies->push('IM identique avec noms differents');
                }
                if ($identityAnomalies->has($identityKey)) {
                    $anomalies->push('Nom/localite relies a plusieurs IM');
                }

                return [
                    'participant_key' => (string) $first['participant_key'],
                    'nom' => (string) $first['nom'],
                    'im' => $im,
                    'localite' => (string) ($first['localite'] ?? 'Non renseigné'),
                    'nb_affectations' => $rows->count(),
                    'nb_activites' => $activities->count(),
                    'activities' => $activities->implode(', '),
                    'montant_total' => (float) $rows->sum('montant'),
                    'anomalie' => $anomalies->implode(' | '),
                ];
            });

        $serviceAverages = $participantEquilibre
            ->groupBy('localite')
            ->map(function (Collection $rows) {
                return [
                    'moyenne_montant' => $rows->count() > 0 ? (float) $rows->avg('montant_total') : 0.0,
                    'moyenne_affectations' => $rows->count() > 0 ? (float) $rows->avg('nb_affectations') : 0.0,
                ];
            });

        $participantEquilibre = $participantEquilibre
            ->map(function (array $row) use ($serviceAverages) {
                $serviceAverage = $serviceAverages->get($row['localite'], [
                    'moyenne_montant' => 0.0,
                    'moyenne_affectations' => 0.0,
                ]);
                $row['ecart_montant_localite'] = (float) $row['montant_total'] - (float) $serviceAverage['moyenne_montant'];
                $row['ecart_affectations_localite'] = (float) $row['nb_affectations'] - (float) $serviceAverage['moyenne_affectations'];

                return $row;
            })
            ->when($balanceLocalite !== '', function (Collection $rows) use ($balanceLocalite) {
                $needle = mb_strtolower($balanceLocalite);

                return $rows->filter(function (array $row) use ($needle) {
                    return str_contains(mb_strtolower($row['localite']), $needle);
                });
            })
            ->sortBy([
                ['localite', 'asc'],
                ['nom', 'asc'],
            ])
            ->values();

        $participantKeys = $participantEquilibre->pluck('participant_key')->all();
        $participantRows = $participantRows
            ->filter(fn (array $row) => in_array($row['participant_key'], $participantKeys, true))
            ->sortBy([
                ['localite', 'asc'],
                ['nom', 'asc'],
                ['examen', 'asc'],
                ['activite', 'asc'],
            ])
            ->values();

        return [
            'participant_equilibre' => $participantEquilibre,
            'participant_rows' => $participantRows,
        ];
    }

    private function documentHeaders(string $document): array
    {
        return match ($document) {
            'decompte' => ['N°', 'ACTIVITE', 'NOM', 'IM', 'LOCALITE DE SERVICE', 'NB JOURS', 'TAUX', 'MONTANT'],
            'presence' => ['N°', 'ACTIVITE', 'NOM', 'IM', 'LOCALITE DE SERVICE', 'CIN', 'SIGNATURE'],
            'decision' => ['N°', 'NOM ET PRENOMS', 'IM', 'LOCALITE DE SERVICE'],
            default => ['EXAMEN', 'ACTIVITE', 'NOM', 'IM', 'LOCALITE DE SERVICE'],
        };
    }

    private function documentLine(array $item, string $document): array
    {
        return match ($document) {
            'decompte' => [
                $item['numero'] ?? '',
                $item['activite'],
                $item['nom'],
                $item['im'],
                $item['localite'],
                $item['jours'],
                $item['taux'] !== null ? number_format((float) $item['taux'], 2, '.', '') : '',
                $item['montant'] !== null ? number_format((float) $item['montant'], 2, '.', '') : '',
            ],
            'presence' => [
                $item['numero'] ?? '',
                $item['activite'],
                $item['nom'],
                $item['im'],
                $item['localite'],
                $item['cin'],
                '',
            ],
            'decision' => [
                $item['numero'] ?? '',
                $item['nom'],
                $item['im'],
                $item['localite'],
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

    private function writeWorksheetHeader(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        string $lastColumn,
        string $documentTitle,
        ?Vacation2026Setting $setting,
        ?Vacation2026Activity $activity
    ): int {
        $line = 1;
        $blocks = array_values(array_filter([
            trim((string) ($setting?->entete ?? '')),
            trim($documentTitle),
            'Activité : '.($activity ? trim($activity->examen.' - '.$activity->libelle) : 'Toutes activités'),
        ], fn ($value) => $value !== ''));

        foreach ($blocks as $index => $block) {
            $sheet->mergeCells("A{$line}:{$lastColumn}{$line}");
            $sheet->setCellValue("A{$line}", $block);
            $sheet->getStyle("A{$line}:{$lastColumn}{$line}")->getAlignment()->setWrapText(true);
            if ($index === 1) {
                $sheet->getStyle("A{$line}:{$lastColumn}{$line}")->getFont()->setBold(true)->setSize(14);
            } else {
                $sheet->getStyle("A{$line}:{$lastColumn}{$line}")->getFont()->setBold($index === 2);
            }
            $line++;
        }

        return $line + 1;
    }

    private function writeDecompteTotalsRow(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        int $line,
        string $label,
        float $montantBrut,
        float $montantIrsa,
        float $montantNet
    ): void {
        $sheet->setCellValue("G{$line}", $label);
        $sheet->setCellValue("H{$line}", number_format($montantBrut, 2, '.', ''));
        $sheet->setCellValue("J{$line}", number_format($montantIrsa, 2, '.', ''));
        $sheet->setCellValue("K{$line}", number_format($montantNet, 2, '.', ''));
        $sheet->getStyle("G{$line}:K{$line}")->getFont()->setBold(true);
    }
}
