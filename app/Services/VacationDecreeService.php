<?php

namespace App\Services;

use App\Models\Vacation2026Activity;
use Carbon\CarbonImmutable;

/**
 * Centralized calculation engine for Décret N°2026-1257 (CEPE / BEPC / EPS 2026).
 *
 * Every personnel requirement is derived from real database figures:
 *   - salles count  (actual repartition_salles rows for the centre)
 *   - candidates    (sum of room effects)
 *   - CISCO count   (per DREN)
 *   - centre count  (per DREN / CISCO)
 *   - centre type   (centre_ecrits.centre_type / centre_corrections.centre_type)
 *   - year          (for exceptional year-specific rules, e.g. CEPE Article 13)
 *
 * All thresholds use ceiling division so that e.g. 1001 candidates = 2 agents
 * (never a decimal fraction).
 */
class VacationDecreeService
{
    public const DECREE = 'Décret N°2026-1257 du 18 mai 2026';

    public const LEVEL_CENTRAL = 'CENTRAL';
    public const LEVEL_DREN = 'DREN';
    public const LEVEL_CISCO = 'CISCO';
    public const LEVEL_CENTRE = 'CENTRE';
    public const LEVEL_EPS = 'EPS';

    public const PHASES = [
        'AVANT_SESSION',
        'PENDANT_SESSION',
        'APRES_SESSION',
        'AVANT_EPREUVES_EPS',
        'PENDANT_EPREUVES_EPS',
        'APRES_EPREUVES_EPS',
    ];

    public const STATUS_BROUILLON = 'BROUILLON';
    public const STATUS_PLANIFIE = 'PLANIFIE';
    public const STATUS_EN_COURS = 'EN COURS';
    public const STATUS_COMPLET = 'COMPLET';
    public const STATUS_INCOMPLET = 'INCOMPLET';
    public const STATUS_VALIDE = 'VALIDE';
    public const STATUS_ANNULE = 'ANNULE';

    public const CENTRE_TYPE_ECRIT = "CENTRE D'ECRIT SEULEMENT";
    public const CENTRE_TYPE_CORRECTION = 'CENTRE DE CORRECTION SEULEMENT';
    public const CENTRE_TYPE_JUMELES = "CENTRE D'ECRIT ET CORRECTION JUMELES";
    public const CENTRE_TYPE_TRANSCRIPTION = 'CENTRE DE TRANSCRIPTION';
    public const CENTRE_TYPE_SOUS = 'SOUS-CENTRE';
    public const CENTRE_TYPE_EPS = 'EPS/GYM';

    public const CENTRE_TYPES = [
        self::CENTRE_TYPE_ECRIT,
        self::CENTRE_TYPE_CORRECTION,
        self::CENTRE_TYPE_JUMELES,
        self::CENTRE_TYPE_TRANSCRIPTION,
        self::CENTRE_TYPE_SOUS,
        self::CENTRE_TYPE_EPS,
    ];

    /**
     * Ceiling division: number of complete tranches needed to cover $value.
     * ceil(1/1000)=1, ceil(1000/1000)=1, ceil(1001/1000)=2, ...
     */
    public function ceilTranche(int $value, int $perTranche): int
    {
        if ($perTranche <= 0) {
            return 0;
        }
        if ($value <= 0) {
            return 0;
        }

        return (int) ceil($value / $perTranche);
    }

    /**
     * Evaluate an activity rule for a given scope context and return
     * [required, days, roles, source].
     *
     * @param  array<string, mixed>  $ctx  candidates, salles, cisco_count,
     *                                     centre_count, centre_type, year,
     *                                     has_special_needs, copies_literary,
     *                                     copies_other, coordinator_count
     * @return array{required:int, days:int, roles:array<int,array{role:string,count:int}>, source:string}
     */
    public function evaluate(Vacation2026Activity $activity, array $ctx = []): array
    {
        $ctx = array_merge([
            'candidates' => 0,
            'salles' => 0,
            'cisco_count' => 0,
            'centre_count' => 0,
            'centre_type' => null,
            'year' => (int) ($activity->year ?: date('Y')),
            'has_special_needs' => false,
            'copies_literary' => null,
            'copies_other' => null,
            'coordinator_count' => 1,
        ], $ctx);

        $candidates = max(0, (int) $ctx['candidates']);
        $salles = max(0, (int) $ctx['salles']);
        $ciscoCount = max(0, (int) $ctx['cisco_count']);
        $centreCount = max(0, (int) $ctx['centre_count']);
        $special = (bool) $ctx['has_special_needs'];
        $year = max(0, (int) $ctx['year']);
        $centreType = (string) ($ctx['centre_type'] ?? '');

        $ruleKey = $activity->rule_key;

        // Fixed-staff / configurable activities (MEN CENTRAL): required is read
        // from the database (max_agents), duration from nb_jours.
        if ($ruleKey === null || $ruleKey === '') {
            return [
                'required' => max(0, (int) $activity->max_agents),
                'days' => max(1, (int) $activity->nb_jours),
                'roles' => [['role' => $activity->libelle, 'count' => max(0, (int) $activity->max_agents)]],
                'source' => $activity->source_rule ?: self::DECREE,
            ];
        }

        return match ($ruleKey) {
            // CISCO
            'cisco_organisation' => $this->ciscoOrganisation($candidates, $activity->nb_jours, $activity),
            'cisco_selection' => $this->ciscoSelection($activity),
            'cisco_followup' => $this->ciscoOrganisation($candidates, 15, $activity),
            'cepe2026_enveloppes' => $this->cepe2026Enveloppes($candidates, $year, $activity),
            'eps_cisco_organisation' => $this->epsCiscoOrganisation($candidates, $activity->nb_jours, $activity),
            'eps_cisco_monitoring' => $this->epsCiscoMonitoring($candidates, $activity->nb_jours, $activity),

            // DREN
            'dren_organisation' => $this->drenOrganisation($centreCount, $activity->nb_jours, $activity),
            'dren_selection_elaboration' => $this->drenSelectionElaboration($ciscoCount, $activity->nb_jours, $activity),
            'dren_selection_regionale' => $this->drenFixed(1, 1, 20, $activity->nb_jours, $activity),
            'dren_finalisation' => $this->drenFixed(4, 12, 0, $activity->nb_jours, $activity),
            'dren_validation' => $this->drenFixed(2, 12, 0, $activity->nb_jours, $activity),
            'dren_testing' => $this->drenFixed(2, 12, 12, $activity->nb_jours, $activity, 'enseignants'),
            'dren_followup' => $this->drenOrganisation($centreCount, 15, $activity),
            'dren_supervision' => $this->drenSupervision($ciscoCount, $activity->nb_jours, $activity),
            'eps_dren_organisation' => $this->epsDren($ciscoCount, $activity->nb_jours, $activity),
            'eps_dren_monitoring' => $this->epsDren($ciscoCount, $activity->nb_jours, $activity),

            // CENTRE
            'centre_before_session' => $this->centreBeforeSession($candidates, $centreType, $activity),
            'centre_session_staff' => $this->centreSessionStaff($salles, $activity->nb_jours, $activity),
            'centre_room_supervisors' => $this->centreRoomSupervisors($salles, $special, $activity->nb_jours, $activity),
            'centre_yard_supervisors' => $this->centreYardSupervisors($salles, $activity->nb_jours, $activity),
            'centre_correction' => $this->centreCorrection($candidates, $ctx, $activity),
            'centre_transcription' => $this->transcription($candidates, $activity->nb_jours, $activity),

            // EPS
            'eps_before' => $this->epsBefore($candidates, $activity),
            'eps_during' => $this->epsDuring($candidates, $activity),
            'eps_after' => $this->epsAfter($candidates, $activity),

            default => [
                'required' => max(0, (int) $activity->max_agents),
                'days' => max(1, (int) $activity->nb_jours),
                'roles' => [['role' => $activity->libelle, 'count' => max(0, (int) $activity->max_agents)]],
                'source' => $activity->source_rule ?: self::DECREE,
            ],
        };
    }
// ---------------------------------------------------------------------
    // CISCO RULES
    // ---------------------------------------------------------------------

    /**
     * Organisation générale CISCO: Chef CISCO 1, Chef de division 1, ATI 1,
     * + 1 agent par tranche de 1000 candidats.
     */
    private function ciscoOrganisation(int $candidates, int $days, Vacation2026Activity $activity): array
    {
        $variable = $this->ceilTranche($candidates, 1000);

        return $this->result($days, $activity, [
            ['role' => 'Chef CISCO', 'count' => 1],
            ['role' => 'Chef de division chargé des examens', 'count' => 1],
            ['role' => 'ATI', 'count' => 1],
            ['role' => 'Agent(s) (1 par tranche de 1000 candidats)', 'count' => $variable],
        ]);
    }

    /**
     * Sélection et élaboration CISCO: Chef CISCO 1, Chef de division 1,
     * 20 agents (12 enseignants, 3 conseillers pédagogiques, 2 secrétaires,
     * 3 agents de sécurité).
     */
    private function ciscoSelection(Vacation2026Activity $activity): array
    {
        return $this->result($activity->nb_jours, $activity, [
            ['role' => 'Chef CISCO', 'count' => 1],
            ['role' => 'Chef de division', 'count' => 1],
            ['role' => 'Enseignants', 'count' => 12],
            ['role' => 'Conseillers pédagogiques', 'count' => 3],
            ['role' => 'Secrétaires', 'count' => 2],
            ['role' => 'Agents de sécurité', 'count' => 3],
        ]);
    }

    /**
     * Article 13 — CEPE 2026 (règle exceptionnelle, uniquement pour l'année 2026).
     *
     *   Durée : 5 jours si candidats > 1600, sinon 3 jours.
     *   Effectif : 1 Président (Chef CISCO) + 1 Vice-Président (Chef division
     *   examens) + 1 ATI + 2 agents sécurité + 1 responsable sous-pli
     *   + 1 agent par tranche de 500 candidats (7 matières).
     */
    private function cepe2026Enveloppes(int $candidates, int $year, Vacation2026Activity $activity): array
    {
        // The rule is exceptional and must NOT automatically apply to other years.
        if ($year !== 2026 || strtoupper((string) $activity->examen) !== 'CEPE') {
            return $this->result(0, $activity, [], 'Règle non applicable cette année (Article 13 — CEPE 2026 uniquement).');
        }

        $days = $candidates > 1600 ? 5 : 3;
        $per500 = $this->ceilTranche($candidates, 500);

        return $this->result($days, $activity, [
            ['role' => 'Président (Chef CISCO)', 'count' => 1],
            ['role' => 'Vice-Président (Chef de division chargé des examens)', 'count' => 1],
            ['role' => 'ATI', 'count' => 1],
            ['role' => 'Agents de sécurité', 'count' => 2],
            ['role' => 'Responsable mise en sous-pli', 'count' => 1],
            ['role' => 'Agent(s) (1 par tranche de 500 candidats, 7 matières)', 'count' => $per500],
        ]);
    }

    /**
     * EPS CISCO organisation : Chef CISCO 1, Chef de division 1, ATI 1,
     * + 1 agent par tranche de 1000 candidats.
     */
    private function epsCiscoOrganisation(int $candidates, int $days, Vacation2026Activity $activity): array
    {
        $variable = $this->ceilTranche($candidates, 1000);

        return $this->result($days, $activity, [
            ['role' => 'Chef CISCO', 'count' => 1],
            ['role' => 'Chef de division chargé des examens', 'count' => 1],
            ['role' => 'ATI', 'count' => 1],
            ['role' => 'Agent(s) (1 par tranche de 1000 candidats)', 'count' => $variable],
        ]);
    }

    /**
     * EPS CISCO monitoring : Chef CISCO 1, Chef de division 1,
     * + 1 agent par tranche de 1000 candidats, duration 15 days.
     */
    private function epsCiscoMonitoring(int $candidates, int $days, Vacation2026Activity $activity): array
    {
        $variable = $this->ceilTranche($candidates, 1000);

        return $this->result($days, $activity, [
            ['role' => 'Chef CISCO', 'count' => 1],
            ['role' => 'Chef de division chargé des examens', 'count' => 1],
            ['role' => 'Agent(s) (1 par tranche de 1000 candidats)', 'count' => $variable],
        ]);
    }

    // ---------------------------------------------------------------------
    // DREN RULES
    // ---------------------------------------------------------------------

    /**
     * Organisation générale DREN: 1 Président (Directeur Régional),
     * 1 Vice-Président (Chef de service chargé des examens), 1 responsable
     * informatique, + 1 agent DREN par tranche de 5 centres d'examen.
     */
    private function drenOrganisation(int $centreCount, int $days, Vacation2026Activity $activity): array
    {
        $variable = $this->ceilTranche($centreCount, 5);

        return $this->result($days, $activity, [
            ['role' => 'Président (Directeur Régional)', 'count' => 1],
            ['role' => 'Vice-Président (Chef de service chargé des examens)', 'count' => 1],
            ['role' => 'Responsable informatique', 'count' => 1],
            ['role' => 'Agent(s) DREN (1 par tranche de 5 centres)', 'count' => $variable],
        ]);
    }

    /**
     * Sélection/élaboration DREN: 1 Président + 1 Vice-Président
     * + 2 agents DREN par CISCO.
     */
    private function drenSelectionElaboration(int $ciscoCount, int $days, Vacation2026Activity $activity): array
    {
        return $this->result($days, $activity, [
            ['role' => 'Président', 'count' => 1],
            ['role' => 'Vice-Président', 'count' => 1],
            ['role' => 'Agents DREN (2 par CISCO)', 'count' => 2 * $ciscoCount],
        ]);
    }

    /**
     * Sélection/élaboration régionale, finalisation/validation/testing
     * provinciaux : 1 Président + 1 Vice-Président + $extra agents.
     */
    private function drenFixed(int $president, int $vicePresident, int $extra, int $days, Vacation2026Activity $activity, string $extraLabel = 'agents'): array
    {
        return $this->result($days, $activity, [
            ['role' => 'Président', 'count' => $president],
            ['role' => 'Vice-Président', 'count' => $vicePresident],
            ['role' => ucfirst($extraLabel), 'count' => $extra],
        ]);
    }

    /**
     * Supervision session/correction/transcription : 2 agents DREN par CISCO.
     */
    private function drenSupervision(int $ciscoCount, int $days, Vacation2026Activity $activity): array
    {
        return $this->result($days, $activity, [
            ['role' => 'Agents DREN (2 par CISCO)', 'count' => 2 * $ciscoCount],
        ]);
    }

    /**
     * EPS DREN : 2 cadres fixes + 2 agents par CISCO.
     */
    private function epsDren(int $ciscoCount, int $days, Vacation2026Activity $activity): array
    {
        return $this->result($days, $activity, [
            ['role' => 'Cadres DREN', 'count' => 2],
            ['role' => 'Agents (2 par CISCO)', 'count' => 2 * $ciscoCount],
        ]);
    }
// ---------------------------------------------------------------------
    // CENTRE RULES
    // ---------------------------------------------------------------------

    /**
     * Préparation avant session (centre) : Chef de centre 1 + Chef adjoint 1
     * + 1 secrétaire par tranche de 250 candidats.
     * Durée selon le type de centre : écrit seul 5 j, correction seul 5 j,
     * jumelés 8 j.
     */
    private function centreBeforeSession(int $candidates, string $centreType, Vacation2026Activity $activity): array
    {
        $days = match ($centreType) {
            self::CENTRE_TYPE_JUMELES => 8,
            default => 5,
        };
        $secretaries = max(1, $this->ceilTranche($candidates, 250));

        return $this->result($days, $activity, [
            ['role' => 'Chef de centre', 'count' => 1],
            ['role' => 'Chef de centre adjoint', 'count' => 1],
            ['role' => 'Secrétaires (1 par tranche de 250 candidats)', 'count' => $secretaries],
        ]);
    }

    /**
     * Encadrement session écrite (pendant session) :
     * Chef de centre 1, adjoint 1, comité de vigilance 1, secrétaire
     * 1 par salle, responsable sécurité des sujets 1, responsables sécurité 2.
     */
    private function centreSessionStaff(int $salles, int $days, Vacation2026Activity $activity): array
    {
        return $this->result($days, $activity, [
            ['role' => 'Chef de centre', 'count' => 1],
            ['role' => 'Chef de centre adjoint', 'count' => 1],
            ['role' => 'Comité de vigilance', 'count' => 1],
            ['role' => 'Secrétaires (1 par salle)', 'count' => $salles],
            ['role' => 'Responsable sécurité des sujets', 'count' => 1],
            ['role' => 'Responsables sécurité', 'count' => 2],
        ]);
    }

    /**
     * Surveillants de salle : 2 par salle (+ 2 si candidats à besoins
     * spécifiques présents). Toujours calculé depuis le nombre réel de salles.
     */
    private function centreRoomSupervisors(int $salles, bool $specialNeeds, int $days, Vacation2026Activity $activity): array
    {
        return $this->result($days, $activity, [
            ['role' => 'Surveillants de salle (2 par salle)', 'count' => 2 * $salles],
            ['role' => 'Surveillants supplémentaires (candidats à besoins spécifiques)', 'count' => $specialNeeds ? 2 : 0],
        ]);
    }

    /**
     * Surveillants de cour : 2 de base + 1 par tranche de 5 salles
     * au-delà de 10 salles. La tranche ne démarre qu'après 10 salles.
     */
    public function yardSupervisors(int $salles): int
    {
        return 2 + $this->ceilTranche(max(0, $salles - 10), 5);
    }

    private function centreYardSupervisors(int $salles, int $days, Vacation2026Activity $activity): array
    {
        return $this->result($days, $activity, [
            ['role' => 'Surveillants de cour', 'count' => $this->yardSupervisors($salles)],
        ]);
    }

    /**
     * Correction : secrétariat 1 agent / 200 candidats + correcteurs selon
     * la cadence de correction + coordonnateur(s).
     *
     * CEPE  : 100 copies/jour x 2 jours.
     * BEPC  : matières littéraires 60 copies/jour x 3 jours,
     *         autres matières 80 copies/jour x 3 jours.
     */
    private function centreCorrection(int $candidates, array $ctx, Vacation2026Activity $activity): array
    {
        $isBepc = strtoupper((string) $activity->examen) === 'BEPC';
        $coordinatorCount = max(1, (int) ($ctx['coordinator_count'] ?? 1));

        $secretaries = $this->ceilTranche($candidates, 200);

        $copiesLiterary = max(0, (int) ($ctx['copies_literary'] ?? $candidates));
        $copiesOther = max(0, (int) ($ctx['copies_other'] ?? $candidates));

        if ($isBepc) {
            $correctorsLiterary = $this->ceilTranche($copiesLiterary, 60 * 3);
            $correctorsOther = $this->ceilTranche($copiesOther, 80 * 3);
            $roles = [
                ['role' => 'Secrétaires de correction (1 par tranche de 200 candidats)', 'count' => $secretaries],
                ['role' => 'Coordonnateurs (1 par matière)', 'count' => $coordinatorCount],
                ['role' => 'Correcteurs matières littéraires (60 copies/jour x 3 jours)', 'count' => $correctorsLiterary],
                ['role' => 'Correcteurs autres matières (80 copies/jour x 3 jours)', 'count' => $correctorsOther],
            ];
        } else {
            $correctors = $this->ceilTranche($candidates, 100 * 2);
            $roles = [
                ['role' => 'Secrétaires de correction (1 par tranche de 200 candidats)', 'count' => $secretaries],
                ['role' => 'Coordonnateurs', 'count' => $coordinatorCount],
                ['role' => 'Correcteurs (100 copies/jour x 2 jours)', 'count' => $correctors],
            ];
        }

        return $this->result($activity->nb_jours, $activity, $roles, self::DECREE . ' — Article 8');
    }

    /**
     * Transcription CEPE et BEPC : 12 agents (2 saisie + 7 lecteurs +
     * 1 responsable papillon + 1 vérificateur + 1 agrafeur)
     * par tranche de 1000 candidats.
     */
    public function transcription(int $candidates, ?int $days, ?Vacation2026Activity $activity = null): array
    {
        $tranches = $this->ceilTranche($candidates, 1000);

        return $this->result($days ?? 5, $activity, [
            ['role' => 'Agents de saisie', 'count' => 2 * $tranches],
            ['role' => 'Lecteurs', 'count' => 7 * $tranches],
            ['role' => 'Responsables papillon', 'count' => 1 * $tranches],
            ['role' => 'Vérificateurs', 'count' => 1 * $tranches],
            ['role' => 'Agrafeurs', 'count' => 1 * $tranches],
        ], self::DECREE . ' — Article 9');
    }
// ---------------------------------------------------------------------
    // EPS RULES
    // ---------------------------------------------------------------------

    private function epsBefore(int $candidates, Vacation2026Activity $activity): array
    {
        return $this->result(4, $activity, [
            ['role' => 'Chef de centre EPS', 'count' => 1],
            ['role' => 'Chef de centre adjoint', 'count' => 1],
            ['role' => 'Secrétaires (1 par tranche de 200 candidats)', 'count' => $this->ceilTranche($candidates, 200)],
        ]);
    }

    /**
     * Épreuves EPS : Chef de centre 1, adjoint 1, surveillants 2,
     * agents de stade 2, médecin 1, + 3 interrogateurs par tranche de
     * 600 candidats. Durée 4 jours, 5 jours si candidats > 3000.
     */
    private function epsDuring(int $candidates, Vacation2026Activity $activity): array
    {
        $days = $candidates > 3000 ? 5 : 4;

        return $this->result($days, $activity, [
            ['role' => 'Chef de centre EPS', 'count' => 1],
            ['role' => 'Chef de centre adjoint', 'count' => 1],
            ['role' => 'Surveillants', 'count' => 2],
            ['role' => 'Agents de stade', 'count' => 2],
            ['role' => 'Médecin', 'count' => 1],
            ['role' => 'Interrogateurs (3 par tranche de 600 candidats)', 'count' => 3 * $this->ceilTranche($candidates, 600)],
        ]);
    }

    private function epsAfter(int $candidates, Vacation2026Activity $activity): array
    {
        return $this->result(4, $activity, [
            ['role' => 'Chef de centre EPS', 'count' => 1],
            ['role' => 'Chef de centre adjoint', 'count' => 1],
            ['role' => 'Secrétaires (1 par tranche de 200 candidats)', 'count' => $this->ceilTranche($candidates, 200)],
        ]);
    }

    // ---------------------------------------------------------------------
    // HELPERS
    // ---------------------------------------------------------------------

    private function result(int $days, ?Vacation2026Activity $activity, array $roles, ?string $source = null): array
    {
        $required = (int) collect($roles)->sum('count');
        $roles = collect($roles)
            ->reject(fn (array $role) => (int) $role['count'] <= 0)
            ->values()
            ->all();

        return [
            'required' => $required,
            'days' => $days,
            'roles' => $roles,
            'source' => $source ?: ($activity?->source_rule ?: self::DECREE),
        ];
    }

    /**
     * Whether an activity with the given rule_key applies to a centre type.
     */
    public function activityAppliesToCentreType(?string $ruleKey, string $centreType): bool
    {
        $centreType = (string) $centreType;

        return match ($ruleKey) {
            'centre_before_session' => in_array($centreType, [
                self::CENTRE_TYPE_ECRIT,
                self::CENTRE_TYPE_CORRECTION,
                self::CENTRE_TYPE_JUMELES,
                self::CENTRE_TYPE_TRANSCRIPTION,
                self::CENTRE_TYPE_SOUS,
            ], true),
            'centre_session_staff', 'centre_room_supervisors', 'centre_yard_supervisors' => in_array($centreType, [
                self::CENTRE_TYPE_ECRIT,
                self::CENTRE_TYPE_JUMELES,
                self::CENTRE_TYPE_SOUS,
            ], true),
            'centre_correction' => in_array($centreType, [
                self::CENTRE_TYPE_CORRECTION,
                self::CENTRE_TYPE_JUMELES,
            ], true),
            'centre_transcription' => in_array($centreType, [
                self::CENTRE_TYPE_TRANSCRIPTION,
                self::CENTRE_TYPE_JUMELES,
            ], true),
            'eps_before', 'eps_during', 'eps_after' => $centreType === self::CENTRE_TYPE_EPS,
            default => true,
        };
    }
/**
     * Calculated status for a (activity, scope) group.
     */
    public function status(int $required, int $assigned, int $validated): string
    {
        $required = max(0, $required);
        $assigned = max(0, $assigned);

        if ($required > 0 && $validated >= $required) {
            return self::STATUS_VALIDE;
        }
        if ($required > 0 && $assigned >= $required) {
            return self::STATUS_COMPLET;
        }
        if ($assigned > 0) {
            return self::STATUS_INCOMPLET;
        }

        return self::STATUS_PLANIFIE;
    }

    /**
     * Number of overlapping days between two inclusive date ranges.
     * Used by the non-cumul control (Article 5 and Article 9).
     */
    public function overlapDays(?string $startA, ?string $endA, ?string $startB, ?string $endB): int
    {
        if ($startA === null || $endA === null || $startB === null || $endB === null) {
            return 0;
        }

        $startA = CarbonImmutable::parse($startA);
        $endA = CarbonImmutable::parse($endA);
        $startB = CarbonImmutable::parse($startB);
        $endB = CarbonImmutable::parse($endB);

        if ($endA->lessThan($startB) || $endB->lessThan($startA)) {
            return 0;
        }

        $overlapStart = $startA->greaterThan($startB) ? $startA : $startB;
        $overlapEnd = $endA->lessThan($endB) ? $endA : $endB;

        return (int) $overlapStart->diffInDays($overlapEnd) + 1;
    }

    /**
     * Inclusive number of days between two dates.
     */
    public function daysBetween(?string $start, ?string $end): int
    {
        if ($start === null || $end === null) {
            return 0;
        }

        $start = CarbonImmutable::parse($start);
        $end = CarbonImmutable::parse($end);

        if ($end->lessThan($start)) {
            return 0;
        }

        return (int) $start->diffInDays($end) + 1;
    }

    /**
     * Rule keys of the computed centre activities applicable to a centre type.
     */
    public function centreActivitiesForType(string $centreType): array
    {
        return array_values(array_filter([
            'centre_before_session',
            'centre_session_staff',
            'centre_room_supervisors',
            'centre_yard_supervisors',
            'centre_correction',
            'centre_transcription',
        ], fn (string $ruleKey) => $this->activityAppliesToCentreType($ruleKey, $centreType)));
    }
}