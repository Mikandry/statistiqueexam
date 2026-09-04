<?php

namespace App\Services;

use App\Models\Vacation2026Activity;
use App\Models\Vacation2026Agent;
use App\Models\Vacation2026Assignment;
use Illuminate\Support\Facades\DB;

/**
 * Handles assignment creation with the decree conflict-control mechanism.
 *
 * Article 5 & 9  : an agent cannot receive two indemnities for activities that
 *                 occur on the same dates, partially overlap or fully overlap.
 * Article 7      : for one examination session an agent can only perform
 *                 indemnified activities at ONE administrative level.
 * Data integrity : an assignment target must belong to the selected scope
 *                 (salle -> centre écrit -> centre correction -> CISCO -> DREN).
 *
 * Hard conflicts (duplicate assignment, admin-level conflict) block the save.
 * Date overlaps are exposed as detailed warnings; they block the save unless
 * the user explicitly forces them (with a documented warning).
 */
class VacationAssignmentService
{
    public function __construct(private readonly VacationDecreeService $decree)
    {
    }

    /**
     * @return array{ok:bool, errors:array<int,string>, warnings:array<int,string>, assignment?:Vacation2026Assignment}
     */
    public function create(array $data): array
    {
        $agentId = (int) ($data['agent_id'] ?? 0);
        $activityId = (int) ($data['activity_id'] ?? 0);
        $level = strtoupper((string) ($data['level'] ?? VacationDecreeService::LEVEL_CENTRAL));
        $drenId = isset($data['dren_id']) && $data['dren_id'] !== '' ? (int) $data['dren_id'] : null;
        $ciscoId = isset($data['cisco_id']) && $data['cisco_id'] !== '' ? (int) $data['cisco_id'] : null;
        $centreCorrectionId = isset($data['centre_correction_id']) && $data['centre_correction_id'] !== '' ? (int) $data['centre_correction_id'] : null;
        $centreEcritId = isset($data['centre_ecrit_id']) && $data['centre_ecrit_id'] !== '' ? (int) $data['centre_ecrit_id'] : null;
        $salleId = isset($data['salle_id']) && $data['salle_id'] !== '' ? (int) $data['salle_id'] : null;
        $startDate = $data['start_date'] ?? null;
        $endDate = $data['end_date'] ?? null;
        $forceOverlap = (bool) ($data['force_overlap'] ?? false);
        $forceLevel = (bool) ($data['force_level'] ?? false);

        $errors = [];
        $warnings = [];

        $agent = Vacation2026Agent::query()->find($agentId);
        $activity = Vacation2026Activity::query()->find($activityId);

        if (! $agent) {
            return ['ok' => false, 'errors' => ['Agent introuvable.'], 'warnings' => []];
        }
        if (! $activity) {
            return ['ok' => false, 'errors' => ['Activité introuvable.'], 'warnings' => []];
        }

        // --- Data integrity: scope chain must be coherent -----------------
        $scopeError = $this->validateScopeChain($drenId, $ciscoId, $centreCorrectionId, $centreEcritId, $salleId);
        if ($scopeError !== null) {
            return ['ok' => false, 'errors' => [$scopeError], 'warnings' => []];
        }

        // --- Duplicate assignment (same agent, activity and scope) --------
        $duplicate = Vacation2026Assignment::query()
            ->where('agent_id', $agentId)
            ->where('activity_id', $activityId)
            ->where('level', $level)
            ->when($drenId, fn ($q) => $q->where('dren_id', $drenId))
            ->when($ciscoId, fn ($q) => $q->where('cisco_id', $ciscoId))
            ->when($centreCorrectionId, fn ($q) => $q->where('centre_correction_id', $centreCorrectionId))
            ->when($centreEcritId, fn ($q) => $q->where('centre_ecrit_id', $centreEcritId))
            ->when($salleId, fn ($q) => $q->where('salle_id', $salleId))
            ->exists();

        if ($duplicate) {
            return [
                'ok' => false,
                'errors' => ["{$agent->nom} est déjà affecté à cette activité sur ce même périmètre (doublon)."],
                'warnings' => [],
            ];
        }

        // --- Article 7: one administrative level per session ---------------
        $sameSessionLevelConflict = Vacation2026Assignment::query()
            ->with(['activity'])
            ->where('agent_id', $agentId)
            ->where('level', '!=', $level)
            ->whereHas('activity', function ($q) use ($activity) {
                $q->where('examen', $activity->examen);
                if ($activity->year) {
                    $q->where('year', $activity->year);
                }
            })
            ->first();

        if ($sameSessionLevelConflict) {
            $conflictActivity = $sameSessionLevelConflict->activity;
            $message = "Conflit Article 7 : {$agent->nom} est déjà affecté au niveau {$sameSessionLevelConflict->level}"
                ." ({$conflictActivity?->libelle}) pour la session {$activity->examen}"
                .($activity->year ? " {$activity->year}" : '').'. Un agent ne peut percevoir qu\'à UN seul niveau administratif pour la même session.';
            if (! $forceLevel) {
                return ['ok' => false, 'errors' => [$message], 'warnings' => []];
            }
            $warnings[] = $message.' (enregistrement forcé).';
        }

        // --- Article 5 & 9: date overlap (non-cumul) -----------------------
        if ($startDate !== null && $startDate !== '') {
            $overlaps = Vacation2026Assignment::query()
                ->with(['activity'])
                ->where('agent_id', $agentId)
                ->whereNotNull('start_date')
                ->whereNotNull('end_date')
                ->get()
                ->filter(function (Vacation2026Assignment $existing) use ($startDate, $endDate) {
                    return $this->decree->overlapDays($startDate, $endDate, $existing->start_date, $existing->end_date) > 0;
                });

            foreach ($overlaps as $existing) {
                $overlap = $this->decree->overlapDays($startDate, $endDate, $existing->start_date, $existing->end_date);
                $warning = "Chevauchement de dates (Article 5/9) : {$existing->start_date} → {$existing->end_date}"
                    ." (activité {$existing->activity?->libelle}, {$overlap} jour(s) en commun).";
                if (! $forceOverlap) {
                    $errors[] = $warning;
                } else {
                    $warnings[] = $warning;
                }
            }
        }

        if (count($errors) > 0) {
            return ['ok' => false, 'errors' => $errors, 'warnings' => $warnings];
        }

        // --- Quota / required personnel check ------------------------------
        $required = (int) ($data['required_personnel'] ?? $activity->max_agents ?? 0);
        if ($required > 0) {
            $assignedInScope = Vacation2026Assignment::query()
                ->where('activity_id', $activityId)
                ->when($drenId, fn ($q) => $q->where('dren_id', $drenId))
                ->when($ciscoId, fn ($q) => $q->where('cisco_id', $ciscoId))
                ->when($centreCorrectionId, fn ($q) => $q->where('centre_correction_id', $centreCorrectionId))
                ->when($centreEcritId, fn ($q) => $q->where('centre_ecrit_id', $centreEcritId))
                ->when($salleId, fn ($q) => $q->where('salle_id', $salleId))
                ->count();

            if ($assignedInScope >= $required) {
                return [
                    'ok' => false,
                    'errors' => ["Limite atteinte pour cette activité/ce périmètre : {$required} agent(s) requis, {$assignedInScope} déjà affecté(s)."],
                    'warnings' => [],
                ];
            }
        }

        // --- Create ---------------------------------------------------------
        $assignment = DB::transaction(function () use (
            $agentId, $activityId, $activity, $level, $drenId, $ciscoId, $centreCorrectionId,
            $centreEcritId, $salleId, $data, $startDate, $endDate
        ) {
            return Vacation2026Assignment::query()->create([
                'agent_id' => $agentId,
                'activity_id' => $activityId,
                'taux' => isset($data['taux']) && $data['taux'] !== '' ? (float) $data['taux'] : null,
                'level' => $level,
                'phase' => $activity->phase,
                'dren_id' => $drenId,
                'cisco_id' => $ciscoId,
                'centre_correction_id' => $centreCorrectionId,
                'centre_ecrit_id' => $centreEcritId,
                'salle_id' => $salleId,
                'role' => $data['role'] ?? null,
                'start_date' => $startDate !== '' ? $startDate : null,
                'end_date' => $endDate !== null && $endDate !== '' ? $endDate : null,
                'nb_jours' => $this->decree->daysBetween($startDate !== '' ? $startDate : null, $endDate !== null && $endDate !== '' ? $endDate : null) ?: null,
                'required_personnel' => isset($data['required_personnel']) ? max(0, (int) $data['required_personnel']) : null,
                'status' => $data['status'] ?? VacationDecreeService::STATUS_PLANIFIE,
                'validated_at' => isset($data['validated']) && (bool) $data['validated'] ? now() : null,
                'notes' => $data['notes'] ?? null,
            ]);
        });

        return [
            'ok' => true,
            'errors' => [],
            'warnings' => $warnings,
            'assignment' => $assignment,
        ];
    }

    /**
     * Validates the scope chain coherence:
     *   salle_id  -> centre_ecrit_id (repartition_salles.centre_ecrit_id)
     *   centre_ecrit_id -> centre_correction_id (centre_ecrits.centre_correction_id)
     *   centre_correction_id -> cisco_id (centre_corrections.cisco_id)
     *   cisco_id -> dren_id (ciscos.dren_id)
     */
    private function validateScopeChain(
        ?int $drenId,
        ?int $ciscoId,
        ?int $centreCorrectionId,
        ?int $centreEcritId,
        ?int $salleId
    ): ?string {
        if ($salleId !== null) {
            $salle = DB::table('repartition_salles')->find($salleId);
            if (! $salle) {
                return 'Salle introuvable.';
            }
            if ($centreEcritId !== null && (int) $salle->centre_ecrit_id !== $centreEcritId) {
                return 'La salle sélectionnée n\'appartient pas au centre écrit choisi.';
            }
        }

        if ($centreEcritId !== null) {
            $centreEcrit = DB::table('centre_ecrits')->find($centreEcritId);
            if (! $centreEcrit) {
                return 'Centre écrit introuvable.';
            }
            if ($centreCorrectionId !== null && (int) $centreEcrit->centre_correction_id !== $centreCorrectionId) {
                return 'Le centre écrit ne dépend pas du centre de correction sélectionné.';
            }
        }

        if ($centreCorrectionId !== null) {
            $centreCorrection = DB::table('centre_corrections')->find($centreCorrectionId);
            if (! $centreCorrection) {
                return 'Centre de correction introuvable.';
            }
            if ($ciscoId !== null && (int) $centreCorrection->cisco_id !== $ciscoId) {
                return 'Le centre de correction ne dépend pas du CISCO sélectionné.';
            }
        }

        if ($ciscoId !== null) {
            $cisco = DB::table('ciscos')->find($ciscoId);
            if (! $cisco) {
                return 'CISCO introuvable.';
            }
            if ($drenId !== null && (int) $cisco->dren_id !== $drenId) {
                return 'Le CISCO sélectionné n\'appartient pas à la DREN choisie.';
            }
        }

        return null;
    }
}