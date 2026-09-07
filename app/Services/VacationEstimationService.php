<?php

namespace App\Services;

use App\Models\CentreCorrection;
use App\Models\CentreEcrit;
use App\Models\Cisco;
use App\Models\Dren;
use App\Models\RepartitionSalle;
use App\Models\Vacation2026Activity;
use App\Models\Vacation2026Assignment;
use Illuminate\Support\Collection;

/**
 * VacationEstimationService
 *
 * Moteur unique d'ESTIMATION AUTOMATIQUE de la Vacation 2026.
 *
 * La chaîne de calcul :
 *   DREN → CISCO → Centre → Salle → Candidats
 *        → Nombre de salles & type de centre
 *        → Agents nécessaires (règles du Décret N°2026-1257)
 *        → Activités (AVANT / PENDANT / APRÈS session, par niveau)
 *        → Nombre de jours (par activité, selon le type de centre)
 *        → Indemnité unitaire (par agent/jour)
 *        → Montant estimé (= agents × jours × indemnité)
 *        → Agrégation : Activité → Centre → CISCO → DREN → Examen → TOTAL
 *
 * Aucune valeur de candidats / salles / agents n'est saisie ici : tout est
 * calculé depuis les données réelles des examens en base de données
 * (repartition_salles, centre_ecrits, centre_corrections, drens, ciscos).
 *
 * Les montants distinguent clairement :
 *   - BESOIN ESTIMÉ   (règles du décret)
 *   - AGENTS AFFECTÉS (affectations réelles saisies)
 *   - ÉCART
 *   - MONTANT ESTIMÉ / VALIDÉ / PAYÉ (jamais confondus).
 */
class VacationEstimationService
{
    /**
     * Indemnité unitaire de référence par agent/jour du Décret N°2026-1257
     * (taux « tout-venant » constaté pour les activités de terrain CEPE/BEPC).
     * Utilisée uniquement lorsque l'activité n'a pas de taux renseigné
     * (taux_activite) ni de taux issu des affectations réelles.
     */
    public const DECREE_RATE_AGENT_JOUR = 20000.00;

    /** Année d'examen prise en compte pour l'estimation. */
    public const EXAM_YEAR = '2026';

    public function __construct(private readonly VacationDecreeService $decree)
    {
    }

    /**
     * Construit l'estimation complète de la Vacation 2026.
     *
     * @param  string  $examFilter        ex. 'CEPE', 'BEPC', '' (tous)
     * @param  string  $phaseFilter       ex. 'AVANT_SESSION', ...
     * @param  int|null  $activityId      une activité précise
     * @param  int|null  $drenId          filtre DREN
     * @param  int|null  $ciscoId         filtre CISCO
     * @param  int|null  $centreId        filtre centre de correction
     * @param  string  $centreTypeFilter  type de centre (écrit / correction / jumelé)
     */
    public function buildEstimate(
        string $examFilter = '',
        string $phaseFilter = '',
        ?int $activityId = null,
        ?int $drenId = null,
        ?int $ciscoId = null,
        ?int $centreId = null,
        string $centreTypeFilter = ''
    ): array {
        // ---- 1. Catalogue d'activités du décret (codes non vides = sans doublon) ----
        $activities = Vacation2026Activity::query()
            ->where('year', self::EXAM_YEAR)
            ->whereNotNull('activity_code')
            ->where('activity_code', '!=', '')
            ->with('groups')
            ->when($examFilter !== '', fn ($q) => $q->where('examen', strtoupper($examFilter)))
            ->when($phaseFilter !== '', fn ($q) => $q->where('phase', $phaseFilter))
            ->when($activityId, fn ($q) => $q->where('id', $activityId))
            ->orderBy('level')->orderBy('examen')->orderBy('ordre')
            ->get();

        $centralActivities = $activities->where('level', VacationDecreeService::LEVEL_CENTRAL)->values();
        $centreActivities = $activities->where('level', VacationDecreeService::LEVEL_CENTRE)->values();
        $ciscoActivities = $activities->where('level', VacationDecreeService::LEVEL_CISCO)->values();
        $drenActivities = $activities->where('level', VacationDecreeService::LEVEL_DREN)->values();
        $epsActivities = $activities->where('level', VacationDecreeService::LEVEL_EPS)->values();

        // Règles applicables à chaque type de centre
        $centreRules = [
            VacationDecreeService::CENTRE_TYPE_ECRIT => $this->decree->centreActivitiesForType(VacationDecreeService::CENTRE_TYPE_ECRIT),
            VacationDecreeService::CENTRE_TYPE_CORRECTION => $this->decree->centreActivitiesForType(VacationDecreeService::CENTRE_TYPE_CORRECTION),
            VacationDecreeService::CENTRE_TYPE_JUMELES => $this->decree->centreActivitiesForType(VacationDecreeService::CENTRE_TYPE_JUMELES),
        ];

        // ---- 2. Données réelles des examens (une seule passe de chargement) ----
        $corrections = CentreCorrection::with('centresEcrit')->get();
        $ecrits = CentreEcrit::all(['id', 'centre_correction_id', 'nom', 'type_examen']);
        $ciscos = Cisco::all(['id', 'dren_id', 'nom']);
        $drens = Dren::all(['id', 'nom']);

        $salles = RepartitionSalle::query()
            ->where('annee', 'like', self::EXAM_YEAR.'%')
            ->get(['id', 'centre_ecrit_id', 'numero_salle', 'effectif', 'has_special_needs_candidates']);
        $salleStats = $this->buildSalleStats($salles);

        $assignments = Vacation2026Assignment::query()->get([
            'id', 'agent_id', 'activity_id', 'taux', 'nb_jours', 'status',
            'dren_id', 'cisco_id', 'centre_correction_id', 'centre_ecrit_id', 'salle_id', 'validated_at',
        ]);
        $assignedByActivity = $this->assignedByActivity($assignments);
        $assignmentRateAvg = $assignments
            ->where('taux', '>', 0)
            ->groupBy('activity_id')
            ->map(fn (Collection $g) => (float) $g->avg('taux') ?: 0.0);

        // ---- 3. Centres groupés (écrit seul / correction seule / jumelés) ----
        $groups = $this->buildCentreGroups($corrections, $ecrits, $salleStats);

        // ---- 4. Estimation complète (centre → cisco → dren → central → eps) ----
        return $this->finalizeBuildEstimate(
            collect(), collect(), $groups, $centreRules, $centreActivities,
            $assignments, $assignedByActivity, $assignmentRateAvg, $activities,
            $centralActivities, $ciscoActivities, $drenActivities, $epsActivities,
            $examFilter, $phaseFilter, $activityId, $drenId, $ciscoId, $centreId, $centreTypeFilter
        );
    }
/**
     * Évalue les niveaux CENTRE / CISCO / DREN / CENTRAL / EPS puis agrège.
     */
    private function finalizeBuildEstimate(
        Collection $rows,
        Collection $centreRows,
        array $groups,
        array $centreRules,
        Collection $centreActivities,
        Collection $assignments,
        array $assignedByActivity,
        Collection $assignmentRateAvg,
        Collection $activities,
        Collection $centralActivities,
        Collection $ciscoActivities,
        Collection $drenActivities,
        Collection $epsActivities,
        string $examFilter,
        string $phaseFilter,
        ?int $activityId,
        ?int $drenId,
        ?int $ciscoId,
        ?int $centreId,
        string $centreTypeFilter
    ): array {
        // ---- Niveau CENTRE ----
        foreach ($groups as $group) {
            if ($centreTypeFilter !== '' && $group['centre_type'] !== $centreTypeFilter) {
                continue;
            }
            if ($drenId !== null && (int) $group['dren_id'] !== $drenId) {
                continue;
            }
            if ($ciscoId !== null && (int) $group['cisco_id'] !== $ciscoId) {
                continue;
            }
            if (! $this->groupMatchesCentre($group, $centreId)) {
                continue;
            }

            foreach ($group['exams'] as $exam => $stats) {
                if (activitySelectionExcludesExam($examFilter, $exam)) {
                    continue;
                }
                $rules = $centreRules[$group['centre_type']] ?? [];

                foreach ($centreActivities as $activity) {
                    if (strtoupper((string) $activity->examen) !== $exam) {
                        continue;
                    }
                    if (! in_array((string) $activity->rule_key, $rules, true)) {
                        continue;
                    }
                    if ($activityId && (int) $activity->id !== $activityId) {
                        continue;
                    }

                    $evaluation = $this->decree->evaluate($activity, [
                        'candidates' => $stats['candidates'],
                        'salles' => $stats['salles'],
                        'centre_type' => $group['centre_type'],
                        'year' => (int) self::EXAM_YEAR,
                        'has_special_needs' => $stats['special'],
                    ]);

                    $row = $this->makeRow(
                        $activity,
                        VacationDecreeService::LEVEL_CENTRE,
                        $evaluation,
                        $assignmentRateAvg[$activity->id] ?? null,
                        [
                            'dren_id' => $group['dren_id'],
                            'dren_nom' => $group['dren_nom'],
                            'cisco_id' => $group['cisco_id'],
                            'cisco_nom' => $group['cisco_nom'],
                            'centre_id' => $group['centre_id'],
                            'centre_nom' => $group['centre_nom'],
                            'centre_type' => $group['centre_type'],
                        ],
                        (int) $stats['candidates'],
                        (int) $stats['salles']
                    );
                    if ($row === null) {
                        continue;
                    }
                    $row['is_centre'] = true;
                    $rows->push($row);
                    $centreRows->push($row);
                }
            }
        }

        // ---- Niveau CISCO ----
        if ($centreId === null) {
            $ciscoStats = $this->aggregateByCisco($groups, $drenId, $ciscoId, $centreId);
        foreach ($ciscoStats as $cid => $cs) {
            foreach ($cs['exams'] as $exam => $stats) {
                if (activitySelectionExcludesExam($examFilter, $exam)) {
                    continue;
                }
                foreach ($ciscoActivities as $activity) {
                    if (strtoupper((string) $activity->examen) !== $exam) {
                        continue;
                    }
                    if ($activityId && (int) $activity->id !== $activityId) {
                        continue;
                    }
                    $evaluation = $this->decree->evaluate($activity, [
                        'candidates' => $stats['candidates'],
                        'salles' => $stats['salles'],
                        'cisco_count' => 1,
                        'centre_count' => $stats['centres'],
                        'year' => (int) self::EXAM_YEAR,
                    ]);
                    $row = $this->makeRow(
                        $activity,
                        VacationDecreeService::LEVEL_CISCO,
                        $evaluation,
                        $assignmentRateAvg[$activity->id] ?? null,
                        [
                            'dren_id' => $cs['dren_id'],
                            'dren_nom' => $cs['dren_nom'],
                            'cisco_id' => $cid,
                            'cisco_nom' => $cs['cisco_nom'],
                            'centre_id' => null,
                            'centre_nom' => '',
                            'centre_type' => '',
                        ],
                        (int) $stats['candidates'],
                        (int) $stats['salles']
                    );
                    if ($row !== null) {
                        $row['is_cisco'] = true;
                        $rows->push($row);
                    }
                }
            }
        }
}
// ---- Niveau DREN ----
        if ($centreId === null && $ciscoId === null) {
            $drenStats = $this->aggregateByDren($groups, $drenId);
        foreach ($drenStats as $did => $ds) {
            foreach ($ds['exams'] as $exam => $stats) {
                if (activitySelectionExcludesExam($examFilter, $exam)) {
                    continue;
                }
                foreach ($drenActivities as $activity) {
                    if (strtoupper((string) $activity->examen) !== $exam) {
                        continue;
                    }
                    if ($activityId && (int) $activity->id !== $activityId) {
                        continue;
                    }
                    $evaluation = $this->decree->evaluate($activity, [
                        'candidates' => $stats['candidates'],
                        'cisco_count' => $stats['ciscos'],
                        'centre_count' => $stats['centres'],
                        'year' => (int) self::EXAM_YEAR,
                    ]);
                    $row = $this->makeRow(
                        $activity,
                        VacationDecreeService::LEVEL_DREN,
                        $evaluation,
                        $assignmentRateAvg[$activity->id] ?? null,
                        [
                            'dren_id' => $did,
                            'dren_nom' => $ds['dren_nom'],
                            'cisco_id' => null,
                            'cisco_nom' => '',
                            'centre_id' => null,
                            'centre_nom' => '',
                            'centre_type' => '',
                        ],
                        (int) $stats['candidates'],
                        (int) $stats['salles']
                    );
                    if ($row !== null) {
                        $row['is_dren'] = true;
                        $rows->push($row);
                    }
                }
            }
        }
        }

        // ---- Niveau MEN CENTRAL (fixe, national, absent si filtre terrain) ----
        if ($drenId === null && $ciscoId === null && $centreId === null) {
            foreach ($centralActivities as $activity) {
                if (activitySelectionExcludesExam($examFilter, (string) $activity->examen)) {
                    continue;
                }
                if ($activityId && (int) $activity->id !== $activityId) {
                    continue;
                }
                $evaluation = $this->decree->evaluate($activity, ['year' => (int) self::EXAM_YEAR]);
                $row = $this->makeRow(
                    $activity,
                    VacationDecreeService::LEVEL_CENTRAL,
                    $evaluation,
                    $assignmentRateAvg[$activity->id] ?? null,
                    null,
                    0,
                    0
                );
                if ($row === null) {
                    continue;
                }
                $row['is_central'] = true;

                if ($activity->groups->isNotEmpty()) {
                    $groupTotal = $activity->groups->map(function ($g) use ($row) {
                        $rate = $this->resolveRate((float) ($g->taux ?? 0), null, null);
                        $gr = $row;
                        $gr['activite'] = $row['activite'].' — '.(string) $g->groupe;
                        $gr['agents'] = max(0, (int) $g->personnel);
                        $gr['jours'] = max(1, (int) $g->nb_jours);
                        $gr['rate'] = $rate;
                        $gr['montant'] = $gr['agents'] * $gr['jours'] * $rate;
                        $gr['groupe'] = (string) $g->groupe;

                        return $gr;
                    });
                    $rows = $rows->merge($groupTotal);
                } else {
                    $rows->push($row);
                }
            }
        }

        // ---- Niveau EPS/GYM ----
        if ($drenId === null && $ciscoId === null && $centreId === null) {
            $skipBepc = activitySelectionExcludesExam($examFilter, 'BEPC');
            $rows = $rows->merge(
                $this->estimateEps($epsActivities, $skipBepc, $activityId, $assignmentRateAvg)
            );
        }

        // ---- Tri pour affichage ----
        $rows = $rows->sortBy([
            ['phase_order', 'asc'],
            ['niveau', 'asc'],
            ['examen', 'asc'],
            ['centre_nom', 'asc'],
            ['activite', 'asc'],
        ])->values();

        return $this->aggregate($rows, $centreRows, $groups, $assignments, $assignedByActivity, $drenId, $ciscoId, $centreId, $centreTypeFilter, $examFilter, $phaseFilter);
    }
/**
     * Agrège les lignes d'estimation et produit le jeu de données final.
     */
    private function aggregate(
        Collection $rows,
        Collection $centreRows,
        array $groups,
        Collection $assignments,
        array $assignedByActivity,
        ?int $drenId,
        ?int $ciscoId,
        ?int $centreId,
        string $centreTypeFilter,
        string $examFilter,
        string $phaseFilter
    ): array {
        // --- Agrégations (examen / phase / activité) ---
        $byExam = $rows->groupBy('examen')->map(fn (Collection $g) => [
            'agents' => $g->sum('agents'),
            'montant' => $g->sum('montant'),
            'activites' => $g->pluck('activite_id')->unique()->count(),
        ])->sortKeys();

        $byPhase = $rows->groupBy('phase')->map(fn (Collection $g) => [
            'agents' => $g->sum('agents'),
            'montant' => $g->sum('montant'),
            'activites' => $g->pluck('activite_id')->unique()->count(),
        ]);

        $byActivity = $rows->groupBy('activite_id')->map(function (Collection $g) use ($assignedByActivity) {
            $first = $g->first();

            return [
                'activite_id' => $first['activite_id'],
                'examen' => $first['examen'],
                'libelle' => $first['activite'],
                'phase' => $first['phase'],
                'niveau' => $first['niveau'],
                'agents' => $g->sum('agents'),
                'jours' => $first['jours'],
                'rate' => $first['rate'],
                'montant' => $g->sum('montant'),
                'assigned' => (int) ($assignedByActivity[$first['activite_id']] ?? 0),
            ];
        })->sortByDesc('montant');

        // --- Par DREN / CISCO / Centre ---
        $byDren = $rows->filter(fn ($r) => $r['dren_id'] !== null && ($r['level_aggreg'] ?? '') === VacationDecreeService::LEVEL_DREN)
            ->groupBy('dren_id')
            ->map(fn (Collection $g) => [
                'dren_id' => $g->first()['dren_id'],
                'dren_nom' => $g->first()['dren_nom'],
                'agents' => $g->sum('agents'),
                'montant' => $g->sum('montant'),
            ])->sortByDesc('montant');

        $byCisco = $rows->filter(fn ($r) => $r['cisco_id'] !== null && ($r['level_aggreg'] ?? '') === VacationDecreeService::LEVEL_CISCO)
            ->groupBy('cisco_id')
            ->map(fn (Collection $g) => [
                'cisco_id' => $g->first()['cisco_id'],
                'cisco_nom' => $g->first()['cisco_nom'],
                'dren_id' => $g->first()['dren_id'],
                'dren_nom' => $g->first()['dren_nom'],
                'agents' => $g->sum('agents'),
                'montant' => $g->sum('montant'),
            ])->sortByDesc('montant');

        $byCentre = $centreRows->groupBy(fn ($r) => $r['centre_id'].'|'.$r['centre_type'])
            ->map(function (Collection $g) {
                $f = $g->first();

                return [
                    'centre_id' => $f['centre_id'],
                    'centre_nom' => $f['centre_nom'],
                    'centre_type' => $f['centre_type'],
                    'cisco_id' => $f['cisco_id'],
                    'cisco_nom' => $f['cisco_nom'],
                    'dren_id' => $f['dren_id'],
                    'dren_nom' => $f['dren_nom'],
                    'candidats' => $f['candidats'],
                    'salles' => $f['salles'],
                    'agents' => $g->sum('agents'),
                    'montant' => $g->sum('montant'),
                ];
            })->sortByDesc('montant');

        // --- Synthèse par activité : besoin estimé / affectés / écart + montants ---
        $synthese = $byActivity->map(function (array $a) use ($assignedByActivity) {
            $assigned = (int) ($assignedByActivity[$a['activite_id']] ?? 0);

            return array_merge($a, [
                'assigned' => $assigned,
                'ecart' => max(0, $a['agents'] - $assigned),
                'montant_valide' => 0.0,
                'montant_paye' => 0.0,
                'completion' => $a['agents'] > 0 ? round(($assigned / $a['agents']) * 100, 1) : 0,
            ]);
        });

        // --- Stats globales (candidats / salles / centres / montants) ---
        $totals = $this->computeTotals(
            $rows,
            $centreRows,
            $groups,
            $assignments,
            $assignedByActivity,
            $byActivity,
            $drenId,
            $ciscoId,
            $centreId,
            $centreTypeFilter,
            $examFilter
        );

        return [
            'rows' => $rows,
            'synthese' => $synthese,
            'by_exam' => $byExam,
            'by_phase' => $byPhase,
            'by_activity' => $byActivity,
            'by_dren' => $byDren,
            'by_cisco' => $byCisco,
            'by_centre' => $byCentre,
            'agents_by_session' => $byPhase,
            'assigned_by_activity' => $assignedByActivity,
            'totals' => $totals,
        ];
    }
/**
     * Calcule les indicateurs globaux : candidats, centres, salles,
     * agents estimés / affectés / restants, montants (estimé / validé / payé),
     * activités totales / réalisées / restantes, et la ventilation par type
     * de centre.
     */
    private function computeTotals(
        Collection $rows,
        Collection $centreRows,
        array $groups,
        Collection $assignments,
        array $assignedByActivity,
        Collection $byActivity,
        ?int $drenId,
        ?int $ciscoId,
        ?int $centreId,
        string $centreTypeFilter,
        string $examFilter
    ): array {
        // --- Filtrage des groupes pour les totaux géographiques ---
        $filteredGroups = collect($groups)->filter(function (array $group) use ($drenId, $ciscoId, $centreId, $centreTypeFilter, $examFilter) {
            if ($centreTypeFilter !== '' && $group['centre_type'] !== $centreTypeFilter) {
                return false;
            }
            if ($drenId !== null && (int) $group['dren_id'] !== $drenId) {
                return false;
            }
            if ($ciscoId !== null && (int) $group['cisco_id'] !== $ciscoId) {
                return false;
            }
            if (! $this->groupMatchesCentre($group, $centreId)) {
                return false;
            }
            if ($examFilter !== '' && ! ($group['has_exam'][strtoupper($examFilter)] ?? false)) {
                return false;
            }

            return true;
        })->values();

        $totalCentres = $filteredGroups->count();
        $totalCandidats = (int) $filteredGroups
            ->where('centre_type', '!=', VacationDecreeService::CENTRE_TYPE_CORRECTION)
            ->sum('candidates_total');
        $totalSalles = (int) $filteredGroups
            ->where('centre_type', '!=', VacationDecreeService::CENTRE_TYPE_CORRECTION)
            ->sum('salles_total');

        $agentsEstimes = (int) $rows->sum('agents');
        $montantEstime = (float) $rows->sum('montant');

        $agentsAffectes = $assignments
            ->when($drenId, fn (Collection $c) => $c->where('dren_id', $drenId))
            ->when($ciscoId, fn (Collection $c) => $c->where('cisco_id', $ciscoId))
            ->when($centreId, fn (Collection $c) => $c->where('centre_correction_id', $centreId))
            ->pluck('agent_id')->filter()->unique()->count();

        // --- Montants validé / payé (jamais confondus avec l'estimation) ---
        $montantValide = (float) $assignments
            ->filter(fn ($a) => in_array(strtoupper((string) $a->status), ['VALIDE', 'VALIDÉ', 'VALIDEES', 'VALIDÉES'], true) || $a->validated_at !== null)
            ->sum(fn ($a) => (float) ($a->taux ?? 0) * max(1, (int) ($a->nb_jours ?? 1)));
        $montantPaye = 0.0;

        // --- Activités totales / réalisées / restantes ---
        $activitiesTotal = $byActivity->count();
        $activitiesRealised = $byActivity->filter(function (array $a) use ($assignedByActivity) {
            $assigned = (int) ($assignedByActivity[$a['activite_id']] ?? 0);

            return $a['agents'] > 0 && $assigned >= $a['agents'];
        })->count();
        $activitiesRemaining = max(0, $activitiesTotal - $activitiesRealised);

        // --- Ventilation par type de centre (écrit / correction / jumelé) ---
        $centreTypes = [
            VacationDecreeService::CENTRE_TYPE_ECRIT => 0,
            VacationDecreeService::CENTRE_TYPE_CORRECTION => 0,
            VacationDecreeService::CENTRE_TYPE_JUMELES => 0,
            'EPS/GYM' => 0,
        ];
        foreach ($filteredGroups as $group) {
            $t = $group['centre_type'];
            if (isset($centreTypes[$t])) {
                $centreTypes[$t]++;
            }
        }

        return [
            'candidats' => $totalCandidats,
            'centres' => $totalCentres,
            'salles' => $totalSalles,
            'agents_estimes' => $agentsEstimes,
            'agents_affectes' => $agentsAffectes,
            'agents_restants' => max(0, $agentsEstimes - $agentsAffectes),
            'montant_estime' => $montantEstime,
            'montant_valide' => $montantValide,
            'montant_paye' => $montantPaye,
            'activites_total' => $activitiesTotal,
            'activites_realisees' => $activitiesRealised,
            'activites_restantes' => $activitiesRemaining,
            'centre_types' => $centreTypes,
        ];
    }
/**
     * Construit une ligne d'estimation (Activité / Phase / Niveau / Agents /
     * Jours / Indemnité unitaire / Montant estimé).
     */
    private function makeRow(
        Vacation2026Activity $activity,
        string $level,
        array $evaluation,
        ?float $assignmentRateAvg,
        ?array $scope,
        int $candidates,
        int $salles
    ): array {
        $agents = max(0, (int) ($evaluation['required'] ?? 0));
        $jours = max(1, (int) ($evaluation['days'] ?? $activity->nb_jours));
        $rate = $this->resolveRate(
            $activity->taux_activite !== null ? (float) $activity->taux_activite : null,
            $assignmentRateAvg,
            null
        );

        $scope ??= [
            'dren_id' => null, 'dren_nom' => '', 'cisco_id' => null, 'cisco_nom' => '',
            'centre_id' => null, 'centre_nom' => '', 'centre_type' => '',
        ];

        return [
            'examen' => (string) $activity->examen,
            'niveau' => $level,
            'activite_id' => (int) $activity->id,
            'activite' => (string) $activity->libelle,
            'phase' => (string) ($activity->phase ?: 'AVANT_SESSION'),
            'phase_order' => $this->phaseOrder($activity->phase),
            'agents' => $agents,
            'jours' => $jours,
            'rate' => $rate,
            'montant' => $agents * $jours * $rate,
            'source_rule' => (string) ($evaluation['source'] ?? $activity->source_rule ?? ''),
            'dren_id' => $scope['dren_id'],
            'dren_nom' => (string) $scope['dren_nom'],
            'cisco_id' => $scope['cisco_id'],
            'cisco_nom' => (string) $scope['cisco_nom'],
            'centre_id' => $scope['centre_id'],
            'centre_nom' => (string) $scope['centre_nom'],
            'centre_type' => (string) $scope['centre_type'],
            'candidats' => $candidates,
            'salles' => $salles,
            'level_aggreg' => $level,
            'roles' => $evaluation['roles'] ?? [],
        ];
    }

    /**
     * Indemnité unitaire (par agent/jour) :
     * taux_activite → moyenne des taux des affectations réelles → taux décret.
     */
    private function resolveRate(?float $activityRate, ?float $assignmentAvg, ?float $fallback): float
    {
        if ($activityRate !== null && $activityRate > 0) {
            return round($activityRate, 2);
        }
        if ($assignmentAvg !== null && $assignmentAvg > 0) {
            return round($assignmentAvg, 2);
        }
        if ($fallback !== null && $fallback > 0) {
            return round($fallback, 2);
        }

        return self::DECREE_RATE_AGENT_JOUR;
    }

    /**
     * Statistiques réelles par centre d'écrit.
     *
     * @return array<int, array{effectif:int, salles:array<string,true>, special:bool}>
     */
    private function buildSalleStats(Collection $salles): array
    {
        $stats = [];
        foreach ($salles as $salle) {
            $ecritId = (int) $salle->centre_ecrit_id;
            if (! isset($stats[$ecritId])) {
                $stats[$ecritId] = ['effectif' => 0, 'salles' => [], 'special' => false];
            }
            $stats[$ecritId]['effectif'] += max(0, (int) $salle->effectif);
            $stats[$ecritId]['salles'][(string) $salle->numero_salle] = true;
            if (! empty($salle->has_special_needs_candidates)) {
                $stats[$ecritId]['special'] = true;
            }
        }

        return $stats;
    }

    /**
     * Groupes centres : regroupement par nom normalisé (même logique que le
     * centre-list) mais en conservant les centres de correction seuls et les
     * jumelés, pour que la correction des copies soit toujours estimée.
     *
     * @return array<string, array{
     *   centre_id:int, centre_nom:string, centre_type:string,
     *   dren_id:?int, dren_nom:string, cisco_id:?int, cisco_nom:string,
     *   correction:?object, ecrits:Collection,
     *   exams:array<string,array{candidates:int,salles:int,special:bool}>,
     *   candidates_total:int, salles_total:int, has_exam:array<string,bool>
     * }>
     */
    private function buildCentreGroups(Collection $corrections, Collection $ecrits, array $salleStats): array
    {
        $groups = [];
        foreach ($corrections as $correction) {
            $key = $this->normName((string) $correction->nom);
            $groups[$key] ??= ['correction' => null, 'ecrits' => collect()];
            $groups[$key]['correction'] = $correction;
        }
        foreach ($ecrits as $ecrit) {
            $key = $this->normName((string) $ecrit->nom);
            $groups[$key] ??= ['correction' => null, 'ecrits' => collect()];
            $groups[$key]['ecrits']->push($ecrit);
        }

        $ciscoById = Cisco::all(['id', 'dren_id', 'nom'])->keyBy('id');
        $drenById = Dren::all(['id', 'nom'])->keyBy('id');

        $built = [];
        foreach ($groups as $key => $group) {
            $correction = $group['correction'];
            $groupEcrits = $group['ecrits'];

            if ($correction && $groupEcrits->isNotEmpty()) {
                $type = VacationDecreeService::CENTRE_TYPE_JUMELES;
                $centreEcrits = $groupEcrits;
            } elseif ($groupEcrits->isNotEmpty()) {
                $type = VacationDecreeService::CENTRE_TYPE_ECRIT;
                $centreEcrits = $groupEcrits;
            } else {
                $type = VacationDecreeService::CENTRE_TYPE_CORRECTION;
                $centreEcrits = $correction->centresEcrit ?? collect();
            }

            // rattachement administratif DREN / CISCO
            $attachedCorrection = $correction;
            if ($attachedCorrection === null && $centreEcrits->isNotEmpty()) {
                $attachedCorrection = $corrections->firstWhere('id', (int) ($centreEcrits->first()->centre_correction_id ?? 0));
            }
            $drenId = null;
            $drenNom = '';
            $ciscoId = null;
            $ciscoNom = '';
            if ($attachedCorrection !== null) {
                $ciscoId = (int) $attachedCorrection->cisco_id;
                $cisco = $ciscoById->get($ciscoId);
                $ciscoNom = (string) ($cisco->nom ?? '');
                $drenId = $cisco ? (int) $cisco->dren_id : null;
                $drenNom = (string) ($drenById->get($drenId)->nom ?? '');
            }

            // statistiques par examen (candidats / salles / besoins spécifiques)
            $exams = [];
            $candidatesTotal = 0;
            $sallesTotal = 0;
            foreach ($centreEcrits as $ecrit) {
                $exam = strtoupper((string) ($ecrit->type_examen ?: 'BEPC'));
                $st = $salleStats[(int) $ecrit->id] ?? null;
                $cand = $st['effectif'] ?? 0;
                $sallesCount = count($st['salles'] ?? []);
                $special = (bool) ($st['special'] ?? false);

                $exams[$exam] ??= ['candidates' => 0, 'salles' => 0, 'special' => false];
                $exams[$exam]['candidates'] += $cand;
                $exams[$exam]['salles'] += $sallesCount;
                if ($special) {
                    $exams[$exam]['special'] = true;
                }

                $candidatesTotal += $cand;
                $sallesTotal += $sallesCount;
            }

            if (empty($exams) && $correction !== null) {
                $exam = strtoupper((string) ($correction->type_examen ?: 'BEPC'));
                $exams[$exam] = ['candidates' => 0, 'salles' => 0, 'special' => false];
            }

            $hasExam = [];
            foreach (array_keys($exams) as $exam) {
                $hasExam[$exam] = true;
            }

            $built[$key] = [
                'centre_id' => $correction ? (int) $correction->id : (int) ($centreEcrits->first()->id ?? 0),
                'centre_nom' => (string) ($correction->nom ?? ($centreEcrits->first()->nom ?? '')),
                'centre_type' => $type,
                'dren_id' => $drenId,
                'dren_nom' => $drenNom,
                'cisco_id' => $ciscoId,
                'cisco_nom' => $ciscoNom,
                'correction' => $correction,
                'ecrits' => $centreEcrits,
                'exams' => $exams,
                'candidates_total' => $candidatesTotal,
                'salles_total' => $sallesTotal,
                'has_exam' => $hasExam,
            ];
        }

        return $built;
    }

    /**
     * Agrégation des candidats / salles / centres par CISCO (par examen).
     */
    private function aggregateByCisco(array $groups, ?int $drenId, ?int $ciscoId, ?int $centreId): array
    {
        $ciscos = Cisco::all(['id', 'dren_id', 'nom'])->keyBy('id');
        $drens = Dren::all(['id', 'nom'])->keyBy('id');
        $result = [];

        foreach ($groups as $group) {
            if (! $this->groupMatchesCentre($group, $centreId)) {
                continue;
            }
            if ($drenId !== null && (int) $group['dren_id'] !== $drenId) {
                continue;
            }
            if ($ciscoId !== null && (int) $group['cisco_id'] !== $ciscoId) {
                continue;
            }
            if ($group['cisco_id'] === null) {
                continue;
            }

            $k = (int) $group['cisco_id'];
            $result[$k] ??= [
                'dren_id' => $group['dren_id'],
                'dren_nom' => $group['dren_nom'],
                'cisco_nom' => (string) ($ciscos->get($k)?->nom ?? ''),
                'exams' => [],
            ];

            foreach ($group['exams'] as $exam => $stats) {
                $result[$k]['exams'][$exam] ??= ['candidates' => 0, 'salles' => 0, 'centres' => 0];
                $result[$k]['exams'][$exam]['candidates'] += (int) $stats['candidates'];
                $result[$k]['exams'][$exam]['salles'] += (int) $stats['salles'];
                $result[$k]['exams'][$exam]['centres']++;
            }
        }

        foreach ($result as $k => &$r) {
            $cisco = $ciscos->get($k);
            if ($cisco !== null) {
                $r['cisco_nom'] = (string) $cisco->nom;
                $r['dren_id'] = (int) $cisco->dren_id;
                $r['dren_nom'] = (string) ($drens->get((int) $cisco->dren_id)?->nom ?? '');
            }
        }

        return $result;
    }

    /**
     * Agrégation des CISCO / centres / candidats par DREN (par examen).
     */
    private function aggregateByDren(array $groups, ?int $drenId): array
    {
        $drens = Dren::all(['id', 'nom'])->keyBy('id');
        $result = [];

        foreach ($groups as $group) {
            if ($drenId !== null && (int) $group['dren_id'] !== $drenId) {
                continue;
            }
            if ($group['dren_id'] === null) {
                continue;
            }
            $k = (int) $group['dren_id'];
            $result[$k] ??= ['dren_nom' => $group['dren_nom'], 'exams' => []];

            foreach ($group['exams'] as $exam => $stats) {
                $result[$k]['exams'][$exam] ??= ['candidates' => 0, 'salles' => 0, 'centres' => 0, 'ciscos' => []];
                $result[$k]['exams'][$exam]['candidates'] += (int) $stats['candidates'];
                $result[$k]['exams'][$exam]['salles'] += (int) $stats['salles'];
                $result[$k]['exams'][$exam]['centres']++;
                if ($group['cisco_id'] !== null) {
                    $result[$k]['exams'][$exam]['ciscos'][(int) $group['cisco_id']] = true;
                }
            }
        }

        foreach ($result as $k => &$r) {
            $r['dren_nom'] = (string) ($drens->get($k)?->nom ?? $r['dren_nom']);
            foreach ($r['exams'] as $exam => &$e) {
                $e['ciscos'] = count($e['ciscos'] ?? []);
            }
        }

        return $result;
    }

    /**
     * Estimation des activités EPS/GYM depuis les centres EPS réels.
     */
    private function estimateEps(
        Collection $epsActivities,
        bool $skipBepc,
        ?int $activityId,
        Collection $assignmentRateAvg
    ): Collection {
        if ($skipBepc || $epsActivities->isEmpty()) {
            return collect();
        }

        $epsCentres = CentreCorrection::query()
            ->where(function ($q) {
                $q->where('is_eps_gym', true)
                    ->orWhere('centre_type', VacationDecreeService::CENTRE_TYPE_EPS);
            })
            ->with('cisco.dren')
            ->get();

        $rows = collect();
        foreach ($epsCentres as $centre) {
            $candidates = max(0, (int) ($centre->eps_capacity ?? 0));
            foreach ($epsActivities as $activity) {
                if ($activityId && (int) $activity->id !== $activityId) {
                    continue;
                }
                $evaluation = $this->decree->evaluate($activity, [
                    'candidates' => $candidates,
                    'salles' => 0,
                    'centre_type' => VacationDecreeService::CENTRE_TYPE_EPS,
                    'year' => (int) self::EXAM_YEAR,
                ]);
                $row = $this->makeRow(
                    $activity,
                    VacationDecreeService::LEVEL_EPS,
                    $evaluation,
                    $assignmentRateAvg[$activity->id] ?? null,
                    [
                        'dren_id' => $centre->cisco ? (int) $centre->cisco->dren_id : null,
                        'dren_nom' => (string) ($centre->cisco->dren->nom ?? ''),
                        'cisco_id' => $centre->cisco ? (int) $centre->cisco_id : null,
                        'cisco_nom' => (string) ($centre->cisco->nom ?? ''),
                        'centre_id' => (int) $centre->id,
                        'centre_nom' => (string) $centre->nom,
                        'centre_type' => VacationDecreeService::CENTRE_TYPE_EPS,
                    ],
                    $candidates,
                    0
                );
                if ($row !== null) {
                    $row['is_eps'] = true;
                    $rows->push($row);
                }
            }
        }

        return $rows;
    }

    /**
     * Nombre d'agents distincts affectés par activité.
     */
    private function assignedByActivity(Collection $assignments): array
    {
        return $assignments
            ->groupBy('activity_id')
            ->map(fn (Collection $g) => $g->pluck('agent_id')->filter()->unique()->count())
            ->all();
    }

    private function normName(string $name): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $name) ?? $name));
    }

    /**
     * Un groupe (centre) appartient-il au centre de correction $centreId ?
     * (soit le centre lui-même, soit un de ses sites d'écrit rattachés)
     */
    private function groupMatchesCentre(array $group, ?int $centreId): bool
    {
        if ($centreId === null) {
            return true;
        }
        if ((int) $group['centre_id'] === $centreId) {
            return true;
        }
        if ($group['correction'] !== null && (int) $group['correction']->id === $centreId) {
            return true;
        }
        foreach ($group['ecrits'] as $ecrit) {
            if ((int) ($ecrit->centre_correction_id ?? 0) === $centreId) {
                return true;
            }
        }

        return false;
    }

    private function phaseOrder(?string $phase): int
    {
        return match ($phase) {
            'AVANT_SESSION', null, '' => 0,
            'PENDANT_SESSION' => 1,
            'APRES_SESSION' => 2,
            'AVANT_EPREUVES_EPS' => 3,
            'PENDANT_EPREUVES_EPS' => 4,
            'APRES_EPREUVES_EPS' => 5,
            default => 6,
        };
    }
}

/**
 * Helper local : l'examen doit-il être exclu de la sélection ?
 */
function activitySelectionExcludesExam(string $examFilter, string $exam): bool
{
    return $examFilter !== '' && strtoupper($examFilter) !== strtoupper($exam);
}