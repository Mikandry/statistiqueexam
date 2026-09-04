<?php

namespace App\Services;

use App\Models\Vacation2026Activity;
use App\Models\Vacation2026Assignment;
use App\Models\Dren;
use App\Models\Cisco;
use App\Models\CentreCorrection;
use App\Models\CentreEcrit;
use App\Models\RepartitionSalle;
use Illuminate\Support\Collection;

/**
 * Vacation2026DashboardService
 *
 * Provides dashboard metrics for the Vacation 2026 module across all administrative
 * levels (MEN Central, DREN, CISCO, Centre, EPS/GYM, Global).
 *
 * All calculations use the existing VacationDecreeService rules and work from
 * actual database data (no mock data).
 */
class Vacation2026DashboardService
{
    public function __construct(private readonly VacationDecreeService $decree)
    {
    }

    /**
     * Get MEN Central Dashboard data (YEAR 2026 only)
     */
    public function menCentralDashboard(string $examFilter = '', string $phaseFilter = '', ?int $activityId = null): array
    {
        $query = Vacation2026Activity::query()
            ->where('level', 'CENTRAL')
            ->where('year', '2026');

        if ($examFilter) {
            $query->where('examen', $examFilter);
        }

        if ($phaseFilter) {
            $query->where('phase', $phaseFilter);
        }
        if ($activityId) {
            $query->where('id', $activityId);
        }

        $activities = $query->get();

        $cepeActivities = $activities->where('examen', 'CEPE');
        $bepcActivities = $activities->where('examen', 'BEPC');

        $cepeStats = $this->getActivitiesStats($cepeActivities);
        $bepcStats = $this->getActivitiesStats($bepcActivities);

        $totalPlanned = $activities->sum(fn($a) => (int)$a->max_agents);
        $totalAssigned = Vacation2026Assignment::query()
            ->whereIn('activity_id', $activities->pluck('id'))
            ->where('level', 'CENTRAL')
            ->distinct('agent_id')
            ->count('agent_id');

        $activitiesByPhase = $activities->groupBy(fn ($activity) => $activity->phase ?: 'AVANT_SESSION')->map(fn($group) => [
            'count' => $group->count(),
            'planned' => $group->sum(fn($a) => (int)$a->max_agents),
            'assigned' => Vacation2026Assignment::query()
                ->whereIn('activity_id', $group->pluck('id'))
                ->where('level', 'CENTRAL')
                ->distinct('agent_id')
                ->count('agent_id'),
        ]);

        $assignmentsByStatus = Vacation2026Assignment::query()
            ->whereIn('activity_id', $activities->pluck('id'))
            ->where('level', 'CENTRAL')
            ->groupBy('status')
            ->selectRaw('status, COUNT(DISTINCT agent_id) as count')
            ->pluck('count', 'status');

        // Compute estimated indemnity
        $estimatedIndemnity = 0.0;
        foreach ($activities as $activity) {
            $rate = $activity->taux_activite ?? 0;
            $days = (int) $activity->nb_jours;
            $groups = $activity->groups;
            if ($groups->isNotEmpty()) {
                $estimatedIndemnity += $groups->sum(fn ($group) => (float) $group->taux * (int) $group->nb_jours * (int) $group->personnel);
            } else {
                $estimatedIndemnity += $rate * $days * (int) $activity->max_agents;
            }
        }

        $activities->load('groups');

        return [
            'cepe' => array_merge($cepeStats, ['examen' => 'CEPE']),
            'bepc' => array_merge($bepcStats, ['examen' => 'BEPC']),
            'total_planned' => $totalPlanned,
            'total_assigned' => $totalAssigned,
            'remaining' => max(0, $totalPlanned - $totalAssigned),
            'activities_by_phase' => $activitiesByPhase,
            'assignments_by_status' => $assignmentsByStatus,
            'completion_percentage' => $totalPlanned > 0 ? round(($totalAssigned / $totalPlanned) * 100, 2) : 0,
            'estimated_indemnity' => $estimatedIndemnity,
            'activities' => $activities,
            'activities_detail' => $this->centralActivitiesDetail($activities),
            'central_groups' => $activities->load('groups')->flatMap(fn ($activity) => $activity->groups->map(fn ($group) => [
                'activity' => $activity->libelle,
                'examen' => $activity->examen,
                'phase' => $activity->phase ?: 'AVANT_SESSION',
                'group' => $group->groupe,
                'id' => $group->id,
                'personnel' => $group->personnel,
                'days' => $group->nb_jours,
                'rate' => (float) $group->taux,
                'amount' => (int) $group->personnel * (int) $group->nb_jours * (float) $group->taux,
            ]))->values(),
        ];
    }

    /**
     * Get DREN Dashboard data
     */
    public function drenDashboard(?int $drenId = null, ?int $ciscoId = null, string $examFilter = '', string $phaseFilter = '', ?int $activityId = null): array
    {
        $drenQuery = Dren::query();

        if ($drenId) {
            $drenQuery->where('id', $drenId);
        }

        $drens = $drenQuery->with(['ciscos', 'vacationAssignments'])->get();

        return $drens->map(function ($dren) use ($ciscoId, $examFilter, $phaseFilter, $activityId) {
            $ciscos = $dren->ciscos;
            if ($ciscoId) {
                $ciscos = $ciscos->where('id', $ciscoId)->values();
            }
            $ciscoIds = $ciscos->pluck('id')->toArray();

            $centresCount = CentreCorrection::query()
                ->whereIn('cisco_id', $ciscoIds)
                ->distinct('id')
                ->count();

            $centreIds = CentreCorrection::query()
                ->whereIn('cisco_id', $ciscoIds)
                ->pluck('id')
                ->toArray();

            $salles = RepartitionSalle::query()
                ->whereHas('centreEcrit', function ($q) use ($centreIds) {
                    $q->whereIn('centre_correction_id', $centreIds);
                })
                ->get();

            $totalCandidates = $salles->sum('effectif');

            $assignments = Vacation2026Assignment::query()
                ->where('dren_id', $dren->id)
                ->when($ciscoId, fn ($query) => $query->where('cisco_id', $ciscoId))
                ->get();

            $activities = $this->dashboardActivities('DREN', $examFilter, $phaseFilter, $activityId);
            $cepeActivities = $activities->where('examen', 'CEPE');
            $bepcActivities = $activities->where('examen', 'BEPC');
            $context = [
                'centre_count' => $centresCount,
                'cisco_count' => $ciscos->count(),
                'salles' => $salles->count(),
                'candidates' => $totalCandidates,
            ];

            $cepeStats = $this->scopedActivitiesStats($cepeActivities, $dren->id, null, 'DREN', $context);
            $bepcStats = $this->scopedActivitiesStats($bepcActivities, $dren->id, null, 'DREN', $context);

            // Compute decree-based required personnel for DREN
            $drenPlanned = 0;
            foreach ($cepeActivities->merge($bepcActivities) as $activity) {
                $ctx = [
                    'centre_count' => $centresCount,
                    'cisco_count' => $ciscos->count(),
                    'candidates' => $totalCandidates,
                    'salles' => $salles->count(),
                    'centre_type' => null,
                    'year' => 2026,
                    'has_special_needs' => false,
                ];
                $eval = $this->decree->evaluate($activity, $ctx);
                $drenPlanned += $eval['required'];
            }

            $totalPlanned = $drenPlanned > 0 ? $drenPlanned : ($cepeStats['planned'] + $bepcStats['planned']);
            $totalAssigned = $cepeStats['assigned'] + $bepcStats['assigned'];
            $phaseSummary = $this->phaseSummary($activities, $dren->id, null, 'DREN', $context);
            $estimatedIndemnity = $this->estimatedIndemnity($activities, $dren->id, null, 'DREN', $context);
            $activityDetails = $this->activityDetails($activities, $dren->id, null, 'DREN', $context);
            $activityBreakdown = $this->drenActivityBreakdown($activities, $dren->id, $context);

            return [
                'dren_id' => $dren->id,
                'dren_name' => $dren->nom,
                'cisco_count' => $ciscos->count(),
                'centre_count' => $centresCount,
                'salle_count' => $salles->count(),
                'candidate_count' => $totalCandidates,
                'cepe' => array_merge($cepeStats, ['examen' => 'CEPE']),
                'bepc' => array_merge($bepcStats, ['examen' => 'BEPC']),
                'total_planned' => $totalPlanned,
                'total_assigned' => $totalAssigned,
                'remaining' => max(0, $totalPlanned - $totalAssigned),
                'assignments_by_status' => $assignments->groupBy('status')->map(fn($g) => $g->count()),
                'completion_percentage' => $totalPlanned > 0 ? round(($totalAssigned / $totalPlanned) * 100, 2) : 0,
                'activities_by_phase' => $phaseSummary,
                'estimated_indemnity' => $estimatedIndemnity,
                'activities' => $activities,
                'activity_details' => $activityDetails,
                'activity_breakdown' => $activityBreakdown,
            ];
        })->values()->all();
    }

    /**
     * Get CISCO Dashboard data
     */
    public function ciscoDashboard(?int $ciscoId = null, string $examFilter = '', string $phaseFilter = '', ?int $activityId = null, ?int $centreId = null): array
    {
        $ciscoQuery = Cisco::query()->with('dren');
        if ($ciscoId) {
            $ciscoQuery->where('id', $ciscoId);
        }

        $ciscos = $ciscoQuery->get();

        return $ciscos->map(function ($cisco) use ($examFilter, $phaseFilter, $activityId, $centreId) {
            $centresCorrection = CentreCorrection::query()->where('cisco_id', $cisco->id)->get();
            if ($centreId) {
                $centresCorrection = $centresCorrection->where('id', $centreId)->values();
            }

            $centreIds = $centresCorrection->pluck('id')->toArray();

            $salles = RepartitionSalle::query()
                ->whereHas('centreEcrit', function ($q) use ($centreIds) {
                    $q->whereIn('centre_correction_id', $centreIds);
                })
                ->get();

            $totalCandidates = $salles->sum('effectif');

            $assignments = Vacation2026Assignment::query()
                ->where('cisco_id', $cisco->id)
                ->when($centreId, fn ($query) => $query->where('centre_correction_id', $centreId))
                ->get();

            $activities = $this->dashboardActivities('CISCO', $examFilter, $phaseFilter, $activityId);
            $cepeActivities = $activities->where('examen', 'CEPE');
            $bepcActivities = $activities->where('examen', 'BEPC');
            $epsActivities = $activities->where('examen', 'EPS');
            $context = ['candidates' => $totalCandidates, 'salles' => $salles->count(), 'centre_count' => $centresCorrection->count(), 'cisco_count' => 1];

            // Compute decree-based required personnel for CISCO
            $ciscoPlanned = 0;
            foreach ($cepeActivities->merge($bepcActivities)->merge($epsActivities) as $activity) {
                $ctx = [
                    'candidates' => $totalCandidates,
                    'salles' => $salles->count(),
                    'cisco_count' => 1,
                    'centre_count' => $centresCorrection->count(),
                    'centre_type' => null,
                    'year' => 2026,
                    'has_special_needs' => false,
                ];
                $eval = $this->decree->evaluate($activity, $ctx);
                $ciscoPlanned += $eval['required'];
            }

            $cepeStats = $this->scopedActivitiesStats($cepeActivities, null, $cisco->id, 'CISCO', $context);
            $bepcStats = $this->scopedActivitiesStats($bepcActivities, null, $cisco->id, 'CISCO', $context);
            $epsStats = $this->scopedActivitiesStats($epsActivities, null, $cisco->id, 'CISCO', $context);

            $totalPlanned = $ciscoPlanned > 0 ? $ciscoPlanned : ($cepeStats['planned'] + $bepcStats['planned'] + $epsStats['planned']);
            $totalAssigned = $cepeStats['assigned'] + $bepcStats['assigned'] + $epsStats['assigned'];
            $phaseSummary = $this->phaseSummary($activities, null, $cisco->id, 'CISCO', $context);
            $estimatedIndemnity = $this->estimatedIndemnity($activities, null, $cisco->id, 'CISCO', $context);
            $activityDetails = $this->activityDetails($activities, null, $cisco->id, 'CISCO', $context);

            return [
                'cisco_id' => $cisco->id,
                'cisco_name' => $cisco->nom,
                'dren_name' => $cisco->dren->nom ?? 'N/A',
                'dren_id' => $cisco->dren_id,
                'centre_count' => $centresCorrection->count(),
                'salle_count' => $salles->count(),
                'total_candidates' => $totalCandidates,
                'cepe' => array_merge($cepeStats, ['examen' => 'CEPE']),
                'bepc' => array_merge($bepcStats, ['examen' => 'BEPC']),
                'eps' => array_merge($epsStats, ['examen' => 'EPS']),
                'total_planned' => $totalPlanned,
                'total_assigned' => $totalAssigned,
                'remaining' => max(0, $totalPlanned - $totalAssigned),
                'assignments_by_status' => $assignments->groupBy('status')->map(fn($g) => $g->count()),
                'completion_percentage' => $totalPlanned > 0 ? round(($totalAssigned / $totalPlanned) * 100, 2) : 0,
                'activities_by_phase' => $phaseSummary,
                'estimated_indemnity' => $estimatedIndemnity,
                'activities' => $activities,
                'activity_details' => $activityDetails,
            ];
        })->values()->all();
    }

    /**
     * Get Centre Dashboard data
     */
    public function centreDashboard(?int $centreId = null, ?int $centreTypeId = null, string $examFilter = '', string $phaseFilter = '', ?int $activityId = null): array
    {
        $corrections = CentreCorrection::query()->with('centresEcrit')->get();
        $ecrits = CentreEcrit::query()->with('centreCorrection')->get();
        $groups = collect();

        foreach ($corrections as $correction) {
            $key = mb_strtolower(trim((string) $correction->nom));
            $groups->put($key, array_merge($groups->get($key, []), [
                'correction' => $correction,
            ]));
        }

        foreach ($ecrits as $ecrit) {
            $key = mb_strtolower(trim((string) $ecrit->nom));
            $group = $groups->get($key, []);
            $group['ecrits'] = collect($group['ecrits'] ?? [])->push($ecrit);
            $groups->put($key, $group);
        }

        return $groups->filter(function (array $group) use ($centreId) {
            $correction = $group['correction'] ?? null;
            $groupEcrits = collect($group['ecrits'] ?? []);

            // A correction record can be only the parent of a differently named
            // written centre. It is not a separate correction site to display.
            if ($correction && $groupEcrits->isEmpty() && $correction->centresEcrit->isNotEmpty()) {
                return false;
            }

            if (!$centreId) {
                return true;
            }

            return ($group['correction']->id ?? null) === $centreId
                || collect($group['ecrits'] ?? [])->contains(fn ($ecrit) => $ecrit->centre_correction_id === $centreId);
        })->map(function (array $group) use ($examFilter, $phaseFilter, $activityId) {
            $centre = $group['correction'] ?? collect($group['ecrits'])->first()->centreCorrection;
            $centresEcrit = collect($group['ecrits'] ?? []);
            $centreEcritIds = $centresEcrit->pluck('id')->all();
            $hasCorrection = isset($group['correction']);
            $hasEcrit = $centresEcrit->isNotEmpty();
            $centreName = $hasCorrection ? $centre->nom : $centresEcrit->first()->nom;
            $centreExam = $centresEcrit->pluck('type_examen')
                ->merge($hasCorrection ? collect([$centre->type_examen]) : collect())
                ->filter()
                ->map(fn ($exam) => strtoupper(trim((string) $exam)))
                ->unique()
                ->first();
            $requestedExam = $examFilter !== '' ? strtoupper(trim($examFilter)) : $centreExam;
            $examMatchesCentre = !$centreExam || $requestedExam === $centreExam;
            $centreType = match (true) {
                $hasCorrection && $hasEcrit => VacationDecreeService::CENTRE_TYPE_JUMELES,
                $hasEcrit => VacationDecreeService::CENTRE_TYPE_ECRIT,
                default => VacationDecreeService::CENTRE_TYPE_CORRECTION,
            };

            $salles = RepartitionSalle::query()
                ->whereIn('centre_ecrit_id', $centreEcritIds)
                ->where('annee', 'like', '2026%')
                ->get();
            if (!$examMatchesCentre) {
                $salles = $salles->filter(fn () => false);
            }

            $totalCandidates = $salles->sum('effectif');
            $totalSalles = $salles->pluck('numero_salle')->filter()->unique()->count();

            $hasSpecialNeeds = $salles->contains(fn($s) => $s->has_special_needs_candidates ?? false);

            $assignments = Vacation2026Assignment::query()
                ->where(function ($query) use ($centre, $centreEcritIds) {
                    $query->where('centre_correction_id', $centre->id ?? 0);
                    if ($centreEcritIds) {
                        $query->orWhereIn('centre_ecrit_id', $centreEcritIds);
                    }
                })
                ->with('activity')
                ->get();

            $activitiesByRole = $assignments->groupBy('role')->map(fn($g) => [
                'count' => $g->unique('agent_id')->count(),
                'status_breakdown' => $g->groupBy('status')->map(fn($sg) => $sg->count()),
            ]);

            $totalAssigned = $assignments->unique('agent_id')->count();

            $activities = Vacation2026Activity::query()
                ->where('level', 'CENTRE')
                ->where('year', '2026')
                ->when($examFilter === '' && $centreExam, fn ($query) => $query->where('examen', $centreExam))
                ->when($examFilter !== '', fn ($query) => $query->where('examen', $examFilter))
                ->when($phaseFilter !== '', fn ($query) => $query->where('phase', $phaseFilter))
                ->when($activityId, fn ($query) => $query->where('id', $activityId))
                ->whereIn('rule_key', $this->decree->centreActivitiesForType($centreType))
                ->orderBy('examen')
                ->orderBy('ordre')
                ->get()
                ->map(function ($activity) use ($centreType, $totalCandidates, $totalSalles, $hasSpecialNeeds, $assignments) {
                    $evaluation = $this->decree->evaluate($activity, [
                        'candidates' => $totalCandidates,
                        'salles' => $totalSalles,
                        'centre_type' => $centreType,
                        'year' => 2026,
                        'has_special_needs' => $hasSpecialNeeds,
                    ]);
                    $assigned = $assignments->where('activity_id', $activity->id)->unique('agent_id')->count();
                    $amount = $evaluation['required'] * (float) ($activity->taux_activite ?? 0) * $evaluation['days'];

                    return [
                        'id' => $activity->id,
                        'examen' => $activity->examen,
                        'libelle' => $activity->libelle,
                        'phase' => $activity->phase,
                        'required' => $evaluation['required'],
                        'assigned' => $assigned,
                        'days' => $evaluation['days'],
                        'rate' => (float) ($activity->taux_activite ?? 0),
                        'amount' => $amount,
                        'roles' => $evaluation['roles'],
                    ];
                });

            $totalPlanned = $activities->sum('required');
            $estimatedIndemnity = $activities->sum('amount');
            $personnelByRole = $activities->flatMap(fn ($activity) => $activity['roles'])
                ->groupBy('role')
                ->map(fn ($roles) => $roles->sum('count'));
            $surveillants = $personnelByRole->filter(fn ($count, $role) => str_contains(mb_strtolower($role), 'surveillants de salle'))->sum();
            $yardSupervisors = $personnelByRole->filter(fn ($count, $role) => str_contains(mb_strtolower($role), 'surveillants de cour'))->sum();
            $secretaries = max(1, $personnelByRole->filter(fn ($count, $role) => str_contains(mb_strtolower($role), '1 par tranche de 250'))->sum());
            $security = $personnelByRole->filter(fn ($count, $role) => str_contains(mb_strtolower($role), 'sécurité'))->sum();
            $activitiesByPhase = $activities->groupBy('phase')->map(fn ($group) => [
                'count' => $group->count(),
                'planned' => $group->sum('required'),
                'assigned' => $group->sum('assigned'),
                'remaining' => max(0, $group->sum('required') - $group->sum('assigned')),
                'amount' => $group->sum('amount'),
            ]);

            // Build a phase-based detail view (avant / pendant / après session).
            // Within each phase the activities are regrouped by exam and the
            // personnel required per the decree is listed by role.
            $phaseSections = collect([
                ['key' => 'AVANT_SESSION', 'label' => 'Avant session', 'phases' => ['AVANT_SESSION', 'AVANT_EPREUVES_EPS']],
                ['key' => 'PENDANT_SESSION', 'label' => 'Pendant session', 'phases' => ['PENDANT_SESSION', 'PENDANT_EPREUVES_EPS']],
                ['key' => 'APRES_SESSION', 'label' => 'Après session', 'phases' => ['APRES_SESSION', 'APRES_EPREUVES_EPS']],
            ]);

            $phases = $phaseSections->map(function (array $section) use ($activities) {
                $sectionActivities = $activities->filter(fn ($activity) => in_array($activity['phase'], $section['phases'], true));

                $exams = $sectionActivities
                    ->groupBy('examen')
                    ->sortKeys()
                    ->map(function (Collection $group, $exam) {
                        $personnelByRole = $group
                            ->flatMap(fn ($activity) => $activity['roles'])
                            ->groupBy('role')
                            ->map(fn ($roles) => $roles->sum('count'))
                            ->sortDesc();

                        return [
                            'examen' => $exam,
                            'planned' => $group->sum('required'),
                            'assigned' => $group->sum('assigned'),
                            'remaining' => max(0, $group->sum('required') - $group->sum('assigned')),
                            'amount' => $group->sum('amount'),
                            'days' => $group->max('days'),
                            'personnel_by_role' => $personnelByRole,
                            'activities' => $group->values(),
                        ];
                    })
                    ->values();

                return array_merge($section, [
                    'activity_count' => $sectionActivities->count(),
                    'planned' => $exams->sum('planned'),
                    'assigned' => $exams->sum('assigned'),
                    'remaining' => max(0, $exams->sum('planned') - $exams->sum('assigned')),
                    'amount' => $exams->sum('amount'),
                    'exams' => $exams,
                ]);
            })->values();

            return [
                'centre_id' => $centre->id ?? null,
                'centre_name' => $centreName,
                'centre_type' => $centreType,
                'is_eps_gym' => ($centre->is_eps_gym ?? false) || $centresEcrit->contains(fn ($ecrit) => $ecrit->is_eps_gym ?? false),
                'is_jumel' => $centreType === VacationDecreeService::CENTRE_TYPE_JUMELES,
                'total_candidates' => $totalCandidates,
                'total_salles' => $totalSalles,
                'has_special_needs' => $hasSpecialNeeds,
                'surveillants_required' => $surveillants,
                'yard_supervisors_required' => $yardSupervisors,
                'secretaries_required' => $secretaries,
                'security_required' => $security,
                'total_planned' => $totalPlanned,
                'total_assigned' => $totalAssigned,
                'remaining' => max(0, $totalPlanned - $totalAssigned),
                'activities_by_role' => $activitiesByRole,
                'assignments_by_status' => $assignments->groupBy('status')->map(fn($g) => $g->count()),
                'completion_percentage' => $totalPlanned > 0 ? round(($totalAssigned / $totalPlanned) * 100, 2) : 0,
                'estimated_indemnity' => $estimatedIndemnity,
                'activities' => $activities,
                'activities_by_phase' => $activitiesByPhase,
                'personnel_by_role' => $personnelByRole,
                'phase_details' => $phases,
            ];
        })->values()->all();
    }

    /**
     * Get EPS/GYM Dashboard data
     */
    public function epsDashboard(string $examFilter = '', string $phaseFilter = '', ?int $activityId = null, ?int $ciscoId = null): array
    {
        $epsActivities = Vacation2026Activity::query()
            ->where('level', 'EPS')
            ->where('year', '2026')
            ->when($examFilter !== '', fn ($query) => $query->where('examen', $examFilter))
            ->when($phaseFilter !== '', fn ($query) => $query->where('phase', $phaseFilter))
            ->when($activityId, fn ($query) => $query->where('id', $activityId))
            ->get();

        $epsCentres = CentreCorrection::query()
            ->where('is_eps_gym', true)
            ->where('type_examen', 'BEPC')
            ->when($ciscoId, fn ($query) => $query->where('cisco_id', $ciscoId))
            ->with('centresEcrit')
            ->get();

        $totalCandidates = 0;
        $defaultCentres = $epsCentres->pluck('cisco_id')->filter()->unique()->count();
        $configuredCentres = $epsCentres->sum(fn ($centre) => max(1, (int) ($centre->eps_capacity ?? 1)));
        $totalCentres = max($defaultCentres, $configuredCentres);
        $centresData = [];

        foreach ($epsCentres as $centre) {
            $centresEcrit = $centre->centresEcrit;
            $centreEcritIds = $centresEcrit->pluck('id')->toArray();

            $salles = RepartitionSalle::query()
                ->whereIn('centre_ecrit_id', $centreEcritIds)
                ->get();

            $candidates = $salles->sum('effectif');
            $totalCandidates += $candidates;

            // Calculate EPS personnel using decree rules
            $interrogators = ceil($candidates / 600) * 3;
            $duration = $candidates > 3000 ? 5 : 4;

            $centresData[] = [
                'centre_id' => $centre->id,
                'centre_name' => $centre->nom,
                'candidates' => $candidates,
                'interrogators_required' => $interrogators,
                'capacity' => max(1, (int) ($centre->eps_capacity ?? 1)),
                'duration' => $duration,
            ];
        }

        $assignments = Vacation2026Assignment::query()
            ->whereIn('centre_correction_id', $epsCentres->pluck('id')->toArray())
            ->where('level', 'EPS')
            ->get();

        // Calculate total required personnel for EPS
        $totalInterrogators = array_sum(array_column($centresData, 'interrogators_required'));
        $secretariat = max(1, $this->decree->ceilTranche($totalCandidates, 200));
        $medical = 1; // Fixed by decree
        $stadium = 2 * $totalCentres; // 2 per centre

        $totalPlanned = $totalInterrogators + $secretariat + $medical + $stadium;
        $totalAssigned = $assignments->unique('agent_id')->count();

        // Compute estimated indemnity
        $estimatedIndemnity = 0.0;
        foreach ($assignments as $assignment) {
            $activity = $assignment->activity;
            if ($activity) {
                $rate = $activity->taux_activite ?? ($assignment->taux ?? 0);
                $days = (int) ($assignment->nb_jours ?? $activity->nb_jours ?? 0);
                $estimatedIndemnity += (float) $rate * $days;
            }
        }

        return [
            'total_candidates' => $totalCandidates,
            'total_centres' => $totalCentres,
            'centres' => $centresData,
            'interrogators_required' => $totalInterrogators,
            'secretariat_required' => $secretariat,
            'medical_required' => $medical,
            'stadium_agents_required' => $stadium,
            'total_planned' => $totalPlanned,
            'total_assigned' => $totalAssigned,
            'remaining' => max(0, $totalPlanned - $totalAssigned),
            'assignments_by_status' => $assignments->groupBy('status')->map(fn($g) => $g->count()),
            'completion_percentage' => $totalPlanned > 0 ? round(($totalAssigned / $totalPlanned) * 100, 2) : 0,
            'estimated_indemnity' => $estimatedIndemnity,
            'activities' => $epsActivities->map(function ($activity) use ($totalCandidates) {
                $evaluation = $activity->max_agents === null
                    ? $this->decree->evaluate($activity, ['candidates' => $totalCandidates, 'year' => 2026])
                    : ['required' => (int) $activity->max_agents, 'days' => (int) $activity->nb_jours];
                return [
                    'examen' => $activity->examen,
                    'libelle' => $activity->libelle,
                    'phase' => $activity->phase,
                    'required' => $evaluation['required'],
                    'days' => $evaluation['days'],
                    'rate' => (float) ($activity->taux_activite ?? 0),
                    'amount' => (int) $evaluation['required'] * (float) ($activity->taux_activite ?? 0) * (int) $evaluation['days'],
                ];
            }),
        ];
    }

    private function dashboardActivities(string $level, string $exam = '', string $phase = '', ?int $activityId = null): Collection
    {
        return Vacation2026Activity::query()
            ->where('level', $level)
            ->where('year', '2026')
            ->when($exam !== '', fn ($query) => $query->where('examen', $exam))
            ->when($phase !== '', fn ($query) => $query->where('phase', $phase))
            ->when($activityId, fn ($query) => $query->where('id', $activityId))
            ->orderBy('phase')
            ->orderBy('ordre')
            ->get();
    }

    private function phaseSummary(Collection $activities, ?int $drenId, ?int $ciscoId, string $level, array $context = []): Collection
    {
        return $activities->groupBy('phase')->map(function (Collection $group) use ($drenId, $ciscoId, $level, $context) {
            $details = $this->activityDetails($group, $drenId, $ciscoId, $level, $context);
            $planned = $details->sum('required');
            $assigned = $details->sum('assigned');
            return [
                'count' => $group->count(),
                'planned' => $planned,
                'assigned' => $assigned,
                'remaining' => max(0, $planned - $assigned),
                'completion_percentage' => $planned > 0 ? round(($assigned / $planned) * 100, 2) : 0,
                'amount' => $details->sum('amount'),
            ];
        });
    }

    private function estimatedIndemnity(Collection $activities, ?int $drenId, ?int $ciscoId, string $level, array $context = []): float
    {
        return (float) $activities->sum(function ($activity) use ($drenId, $ciscoId, $level, $context) {
            $assigned = Vacation2026Assignment::query()
                ->where('activity_id', $activity->id)
                ->when($drenId, fn ($query) => $query->where('dren_id', $drenId))
                ->when($ciscoId, fn ($query) => $query->where('cisco_id', $ciscoId))
                ->where('level', $level)
                ->distinct('agent_id')
                ->count('agent_id');
            $personnel = $activity->max_agents !== null
                ? (int) $activity->max_agents
                : $this->decree->evaluate($activity, array_merge($context, ['year' => 2026]))['required'];
            return $personnel * (float) ($activity->taux_activite ?? 0) * (int) $activity->nb_jours;
        });
    }

    private function activityDetails(Collection $activities, ?int $drenId, ?int $ciscoId, string $level, array $context = []): Collection
    {
        return $activities->map(function ($activity) use ($drenId, $ciscoId, $level, $context) {
            $evaluation = $activity->max_agents === null
                ? $this->decree->evaluate($activity, array_merge($context, ['year' => 2026]))
                : ['required' => (int) $activity->max_agents, 'days' => (int) $activity->nb_jours];
            $assigned = Vacation2026Assignment::query()
                ->where('activity_id', $activity->id)
                ->when($drenId, fn ($query) => $query->where('dren_id', $drenId))
                ->when($ciscoId, fn ($query) => $query->where('cisco_id', $ciscoId))
                ->where('level', $level)
                ->distinct('agent_id')
                ->count('agent_id');

            return [
                'examen' => $activity->examen,
                'libelle' => $activity->libelle,
                'phase' => $activity->phase,
                'required' => (int) $evaluation['required'],
                'assigned' => $assigned,
                'days' => (int) ($evaluation['days'] ?? $activity->nb_jours),
                'rate' => (float) ($activity->taux_activite ?? 0),
                'amount' => (int) $evaluation['required'] * (float) ($activity->taux_activite ?? 0) * (int) ($evaluation['days'] ?? $activity->nb_jours),
            ];
        });
    }

    private function scopedActivitiesStats(Collection $activities, ?int $drenId, ?int $ciscoId, string $level, array $context = []): array
    {
        $details = $this->activityDetails($activities, $drenId, $ciscoId, $level, $context);
        $planned = $details->sum('required');
        $assigned = $details->sum('assigned');
        return [
            'planned' => $planned,
            'assigned' => $assigned,
            'remaining' => max(0, $planned - $assigned),
            'completion_percentage' => $planned > 0 ? round(($assigned / $planned) * 100, 2) : 0,
        ];
    }

    /**
     * Get Global Vacation 2026 Dashboard data
     */
    public function globalDashboard(string $examFilter = '', string $phaseFilter = '', ?int $activityId = null, ?int $drenId = null, ?int $ciscoId = null, ?int $centreId = null): array
    {
        // Get all activities for 2026
        $allActivities = Vacation2026Activity::query()
            ->where('year', '2026')
            ->when($centreId || $ciscoId || $drenId, function ($query) use ($centreId, $ciscoId, $drenId) {
                $query->where('level', $centreId ? 'CENTRE' : ($ciscoId ? 'CISCO' : 'DREN'));
            })
            ->when($examFilter !== '', fn ($query) => $query->where('examen', $examFilter))
            ->when($phaseFilter !== '', fn ($query) => $query->where('phase', $phaseFilter))
            ->when($activityId, fn ($query) => $query->where('id', $activityId))
            ->get();

        $cepeActivities = $allActivities->where('examen', 'CEPE');
        $bepcActivities = $allActivities->where('examen', 'BEPC');
        $epsActivities = $allActivities->where('examen', 'EPS');

        $cepeStats = $this->getActivitiesStats($cepeActivities);
        $bepcStats = $this->getActivitiesStats($bepcActivities);
        $epsStats = $this->getActivitiesStats($epsActivities);

        // Get centres and candidates data
        $allCentres = CentreCorrection::query()->get();
        $allSalles = RepartitionSalle::query()
            ->when($centreId, fn ($query) => $query->whereHas('centreEcrit', fn ($q) => $q->where('centre_correction_id', $centreId)))
            ->when($ciscoId, fn ($query) => $query->whereHas('centreEcrit.centreCorrection', fn ($q) => $q->where('cisco_id', $ciscoId)))
            ->when($drenId, fn ($query) => $query->whereHas('centreEcrit.centreCorrection.cisco', fn ($q) => $q->where('dren_id', $drenId)))
            ->get();

        $totalCandidates = $allSalles->sum('effectif');
        $totalCentres = $allCentres->count();
        $totalSalles = $allSalles->count();

        // Get all assignments
        $allAssignments = Vacation2026Assignment::query()
            ->when($centreId, fn ($query) => $query->where('centre_correction_id', $centreId))
            ->when($ciscoId, fn ($query) => $query->where('cisco_id', $ciscoId))
            ->when($drenId, fn ($query) => $query->where('dren_id', $drenId))
            ->get();

        $totalPlanned = $cepeStats['planned'] + $bepcStats['planned'] + $epsStats['planned'];
        $totalAssigned = $allAssignments->unique('agent_id')->count();

        // Compute estimated indemnity
        $estimatedIndemnity = 0.0;
        foreach ($allActivities as $activity) {
            $rate = $activity->taux_activite ?? 0;
            $days = (int) $activity->nb_jours;
            $estimatedIndemnity += $rate * $days * (int) $activity->max_agents;
        }

        // Activities by status
        $activitiesByStatus = $allActivities->groupBy(function ($activity) {
            $assignments = Vacation2026Assignment::query()
                ->where('activity_id', $activity->id)
                ->get();

            if ($assignments->isEmpty()) {
                return 'PENDING';
            }

            $statusCounts = $assignments->groupBy('status')->map(fn($g) => $g->count());
            if ($statusCounts->has('COMPLET')) {
                return 'COMPLETED';
            } elseif ($statusCounts->has('EN COURS')) {
                return 'IN_PROGRESS';
            }

            return 'PENDING';
        })->map(fn($g) => $g->count());

        $activitiesByPhase = $allActivities->groupBy(fn ($activity) => $activity->phase ?: 'AVANT_SESSION')->map(function ($group) {
            $planned = $group->sum(fn ($activity) => (int) ($activity->max_agents ?? 0));
            $assigned = Vacation2026Assignment::query()
                ->whereIn('activity_id', $group->pluck('id'))
                ->distinct('agent_id')
                ->count('agent_id');
            return [
                'count' => $group->count(),
                'planned' => $planned,
                'assigned' => $assigned,
                'remaining' => max(0, $planned - $assigned),
                'amount' => $group->sum(fn ($activity) => (int) ($activity->max_agents ?? 0) * (float) ($activity->taux_activite ?? 0) * (int) $activity->nb_jours),
            ];
        });

        return [
            'cepe' => array_merge($cepeStats, ['examen' => 'CEPE']),
            'bepc' => array_merge($bepcStats, ['examen' => 'BEPC']),
            'eps' => array_merge($epsStats, ['examen' => 'EPS']),
            'total_candidates' => $totalCandidates,
            'total_centres' => $totalCentres,
            'total_salles' => $totalSalles,
            'total_planned' => $totalPlanned,
            'total_assigned' => $totalAssigned,
            'remaining' => max(0, $totalPlanned - $totalAssigned),
            'activities_by_status' => $activitiesByStatus,
            'assignments_by_status' => $allAssignments->groupBy('status')->map(fn($g) => $g->count()),
            'activities_by_phase' => $activitiesByPhase,
            'completion_percentage' => $totalPlanned > 0 ? round(($totalAssigned / $totalPlanned) * 100, 2) : 0,
            'estimated_indemnity' => $estimatedIndemnity,
        ];
    }

    /**
     * Helper: Build a per-activity detail list (read-only) for the MEN Central
     * dashboard, exposing estimated / assigned / remaining / days / rate / amount
     * per central activity (mirrors the centre dashboard activity table).
     */
    private function centralActivitiesDetail(Collection $activities): Collection
    {
        $ids = $activities->pluck('id');

        if ($ids->isEmpty()) {
            return collect();
        }

        $assignmentsCounts = Vacation2026Assignment::query()
            ->whereIn('activity_id', $ids)
            ->where('level', 'CENTRAL')
            ->groupBy('activity_id')
            ->selectRaw('activity_id, COUNT(DISTINCT agent_id) as total')
            ->pluck('total', 'activity_id');

        return $activities
            ->map(function ($activity) use ($assignmentsCounts) {
                $required = (int) $activity->max_agents;
                $assigned = (int) ($assignmentsCounts[$activity->id] ?? 0);
                $days = (int) $activity->nb_jours;
                $rate = (float) ($activity->taux_activite ?? 0);
                $amount = 0.0;

                $groups = $activity->groups;
                if ($groups->isNotEmpty()) {
                    $amount = $groups->sum(fn ($group) => (float) $group->taux * (int) $group->nb_jours * (int) $group->personnel);
                } else {
                    $amount = $rate * $days * $required;
                }

                return [
                    'id' => $activity->id,
                    'examen' => $activity->examen,
                    'libelle' => $activity->libelle,
                    'phase' => $activity->phase ?: 'AVANT_SESSION',
                    'required' => $required,
                    'assigned' => $assigned,
                    'remaining' => max(0, $required - $assigned),
                    'days' => $days,
                    'rate' => $rate,
                    'amount' => round($amount),
                ];
            })
            ->values();
    }

    /**
     * Helper: Build a rich per-activity breakdown for the DREN dashboard,
     * exposing the role-by-role composition of the required personnel with a
     * human-readable explanation of each computed number (centres / CISCOs /
     * tranches) according to the decree.
     */
    private function drenActivityBreakdown(Collection $activities, ?int $drenId, array $context = []): Collection
    {
        $centres = max(0, (int) $context['centre_count']);
        $ciscos = max(0, (int) $context['cisco_count']);
        $candidates = max(0, (int) $context['candidates']);

        return $activities->map(function ($activity) use ($drenId, $context, $centres, $ciscos, $candidates) {
            $evaluation = $activity->max_agents === null
                ? $this->decree->evaluate($activity, array_merge($context, ['year' => 2026]))
                : [
                    'required' => (int) $activity->max_agents,
                    'days' => (int) $activity->nb_jours,
                    'roles' => [['role' => $activity->libelle, 'count' => (int) $activity->max_agents]],
                ];

            $assigned = Vacation2026Assignment::query()
                ->where('activity_id', $activity->id)
                ->when($drenId, fn ($q) => $q->where('dren_id', $drenId))
                ->where('level', 'DREN')
                ->distinct('agent_id')
                ->count('agent_id');

            $roles = collect($evaluation['roles'])
                ->map(function (array $role) use ($activity, $centres, $ciscos, $candidates) {
                    return array_merge($role, [
                        'note' => $this->explainDrenRole((string) ($activity->rule_key ?? ''), $role, $centres, $ciscos, $candidates),
                    ]);
                })
                ->values();

            return [
                'examen' => $activity->examen,
                'libelle' => $activity->libelle,
                'phase' => $activity->phase ?: 'AVANT_SESSION',
                'required' => (int) $evaluation['required'],
                'assigned' => $assigned,
                'remaining' => max(0, (int) $evaluation['required'] - $assigned),
                'days' => (int) ($evaluation['days'] ?? $activity->nb_jours),
                'rate' => (float) ($activity->taux_activite ?? 0),
                'amount' => (int) $evaluation['required'] * (float) ($activity->taux_activite ?? 0) * (int) ($evaluation['days'] ?? $activity->nb_jours),
                'roles' => $roles,
            ];
        })->values();
    }

    /**
     * Build a short French explanation for a DREN role count according to the
     * decree rules (tranches de 5 centres, 2 agents par CISCO, fixe, ...).
     */
    private function explainDrenRole(string $ruleKey, array $role, int $centres, int $ciscos, int $candidates): string
    {
        $label = strtolower((string) ($role['role'] ?? ''));
        $count = max(0, (int) ($role['count'] ?? 0));

        if (str_contains($label, 'tranche de 5 centres')) {
            $tranches = $this->decree->ceilTranche($centres, 5);
            return "{$centres} centre(s) sous protection ÷ 5 centres = {$tranches} tranche(s), soit {$count} agent(s) (toute tranche entamée compte pour 1).";
        }
        if (str_contains($label, 'par cisco')) {
            return "{$ciscos} CISCO × 2 agents = {$count} agent(s).";
        }
        if (str_contains($label, 'candidats') && $candidates > 0) {
            $tranches = $this->decree->ceilTranche($candidates, 1000);
            return "{$candidates} candidat(s) ÷ 1000 = {$tranches} tranche(s), soit {$count} agent(s).";
        }

        return 'Nombre fixé par le décret N°2026-1257.';
    }

    /**
     * Helper: Get statistics for a collection of activities
     */
    private function getActivitiesStats(Collection $activities, ?int $drenId = null, ?int $ciscoId = null, ?string $level = null): array
    {
        $plannedCount = $activities->sum(fn($a) => (int)$a->max_agents);

        $assignmentQuery = Vacation2026Assignment::query()
            ->whereIn('activity_id', $activities->pluck('id'));

        if ($drenId) {
            $assignmentQuery->where('dren_id', $drenId);
        }

        if ($ciscoId) {
            $assignmentQuery->where('cisco_id', $ciscoId);
        }

        if ($level) {
            $assignmentQuery->where('level', $level);
        }

        $assignedCount = $assignmentQuery->distinct('agent_id')->count();

        return [
            'planned' => $plannedCount,
            'assigned' => $assignedCount,
            'remaining' => max(0, $plannedCount - $assignedCount),
            'completion_percentage' => $plannedCount > 0 ? round(($assignedCount / $plannedCount) * 100, 2) : 0,
        ];
    }
}
