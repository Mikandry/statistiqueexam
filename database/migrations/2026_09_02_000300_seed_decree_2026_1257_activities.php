<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the database-driven rule catalogue for Décret N°2026-1257
 * (CEPE / BEPC / EPS 2026). Idempotent: an activity is only inserted when its
 * stable activity_code does not already exist, so it never duplicates or
 * overwrites user data.
 *
 * - level = CENTRAL | DREN | CISCO | CENTRE | EPS
 * - For CENTRAL activities max_agents = fixed "required personnel" from the
 *   decree (editable in the application).
 * - For DREN/CISCO/CENTRE/EPS activities max_agents is NULL: the required
 *   personnel is computed live by VacationDecreeService from the rule_key and
 *   the actual database figures (candidates / salles / CISCO count / type de
 *   centre / year).
 */
return new class extends Migration
{
    private const SOURCE = 'Décret N°2026-1257 du 18 mai 2026';

    public function up(): void
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('vacation_2026_activities')) {
            return;
        }

        // -----------------------------------------------------------------
        // 1. MEN CENTRAL — CEPE
        // -----------------------------------------------------------------
        $this->activity('CEPE', "Travaux de finalisation des sujets", 25, 10, 10, 'CENTRAL', 'AVANT_SESSION', 'CEPE_CENTRAL_FINALISATION', null, self::SOURCE . ' — Article 4');
        $this->activity('CEPE', 'Travaux de validation des sujets', 31, 5, 20, 'CENTRAL', 'AVANT_SESSION', 'CEPE_CENTRAL_VALIDATION', null, self::SOURCE . ' — Article 4');
        $this->activity('CEPE', 'Travaux de testing des sujets', 12, 3, 30, 'CENTRAL', 'AVANT_SESSION', 'CEPE_CENTRAL_TESTING', null, self::SOURCE . ' — Article 4');
        $this->activity('CEPE', 'Travaux de choix des sujets', 26, 3, 40, 'CENTRAL', 'AVANT_SESSION', 'CEPE_CENTRAL_CHOIX', null, self::SOURCE . ' — Article 4');
        $this->activity('CEPE', 'Travaux de retouche', 10, 2, 50, 'CENTRAL', 'AVANT_SESSION', 'CEPE_CENTRAL_RETOUCHE', null, self::SOURCE . ' — Article 4');
        $this->activity('CEPE', 'Travaux de préparation des enveloppes', 30, 5, 60, 'CENTRAL', 'AVANT_SESSION', 'CEPE_CENTRAL_PREP_ENVELOPPES', null, self::SOURCE . ' — Article 4');
        $this->activity('CEPE', 'Travaux informatiques', 20, 10, 70, 'CENTRAL', 'PENDANT_SESSION', 'CEPE_CENTRAL_INFORMATIQUE', null, self::SOURCE . ' — Article 4');
        $this->activity('CEPE', 'Dispatching des sujets', 40, 3, 80, 'CENTRAL', 'AVANT_SESSION', 'CEPE_CENTRAL_DISPATCH', null, self::SOURCE . ' — Article 4');
        $this->activity('CEPE', 'Travaux de préparation des sujets', 15, 2, 90, 'CENTRAL', 'AVANT_SESSION', 'CEPE_CENTRAL_PREP_SUJETS', null, self::SOURCE . ' — Article 4');
        $this->activity('CEPE', 'Traitement des données', 42, 8, 100, 'CENTRAL', 'APRES_SESSION', 'CEPE_CENTRAL_TRAITEMENT', null, self::SOURCE . ' — Article 4');
        $this->activity('CEPE', 'Publication des résultats', 20, 3, 110, 'CENTRAL', 'APRES_SESSION', 'CEPE_CENTRAL_PUBLICATION', null, self::SOURCE . ' — Article 4');
        $this->activity('CEPE', 'Supervision', 12, 6, 120, 'CENTRAL', 'PENDANT_SESSION', 'CEPE_CENTRAL_SUPERVISION', null, self::SOURCE . ' — Article 4');

        // -----------------------------------------------------------------
        // 2. MEN CENTRAL — BEPC
        // -----------------------------------------------------------------
        $this->activity('BEPC', "Répartition et envoi des feuilles d'examen", 30, 5, 10, 'CENTRAL', 'AVANT_SESSION', 'BEPC_CENTRAL_REPARTITION_FEUILLES', null, self::SOURCE . ' — Article 4');
        $this->activity('BEPC', 'Travaux informatiques', 20, 15, 20, 'CENTRAL', 'PENDANT_SESSION', 'BEPC_CENTRAL_INFORMATIQUE', null, self::SOURCE . ' — Article 4');
        $this->activity('BEPC', 'Supervision de la sélection et élaboration régionale', 15, 5, 30, 'CENTRAL', 'AVANT_SESSION', 'BEPC_CENTRAL_SUPERVISION_SELECTION', null, self::SOURCE . ' — Article 4');
        $this->activity('BEPC', 'Sélection et élaboration des sujets au niveau national', 125, 5, 40, 'CENTRAL', 'AVANT_SESSION', 'BEPC_CENTRAL_SELECTION_NATIONALE', null, self::SOURCE . ' — Article 4');
        $this->activity('BEPC', 'Préparation des livres et enveloppes', 80, 10, 50, 'CENTRAL', 'AVANT_SESSION', 'BEPC_CENTRAL_PREP_LIVRES', null, self::SOURCE . ' — Article 4');
        $this->activity('BEPC', 'Dispatching des sujets', 40, 3, 60, 'CENTRAL', 'AVANT_SESSION', 'BEPC_CENTRAL_DISPATCH', null, self::SOURCE . ' — Article 4');
        $this->activity('BEPC', 'Choix des sujets', 26, 3, 70, 'CENTRAL', 'AVANT_SESSION', 'BEPC_CENTRAL_CHOIX', null, self::SOURCE . ' — Article 4');
        $this->activity('BEPC', 'Préparation des sujets', 20, 2, 80, 'CENTRAL', 'AVANT_SESSION', 'BEPC_CENTRAL_PREP_SUJETS', null, self::SOURCE . ' — Article 4');
        $this->activity('BEPC', 'Traitement des données', 42, 15, 90, 'CENTRAL', 'APRES_SESSION', 'BEPC_CENTRAL_TRAITEMENT', null, self::SOURCE . ' — Article 4');
        $this->activity('BEPC', 'Publication des résultats', 20, 3, 100, 'CENTRAL', 'APRES_SESSION', 'BEPC_CENTRAL_PUBLICATION', null, self::SOURCE . ' — Article 4');
        $this->activity('BEPC', 'Supervision', 12, 6, 110, 'CENTRAL', 'PENDANT_SESSION', 'BEPC_CENTRAL_SUPERVISION', null, self::SOURCE . ' — Article 4');
    
// -----------------------------------------------------------------
        // 3. DREN — CEPE
        // -----------------------------------------------------------------
        $this->activity('CEPE', 'Organisation générale', null, 5, 10, 'DREN', 'AVANT_SESSION', 'CEPE_DREN_ORGANISATION', 'dren_organisation', self::SOURCE . ' — Article 5');
        $this->activity('CEPE', 'Sélection et élaboration des sujets', null, 5, 20, 'DREN', 'AVANT_SESSION', 'CEPE_DREN_SELECTION_ELABORATION', 'dren_selection_elaboration', self::SOURCE . ' — Article 5');
        $this->activity('CEPE', 'Sélection et élaboration régionale', null, 5, 30, 'DREN', 'AVANT_SESSION', 'CEPE_DREN_SELECTION_REGIONALE', 'dren_selection_regionale', self::SOURCE . ' — Article 5');
        $this->activity('CEPE', 'Finalisation provinciale', null, 5, 40, 'DREN', 'AVANT_SESSION', 'CEPE_DREN_FINALISATION', 'dren_finalisation', self::SOURCE . ' — Article 5');
        $this->activity('CEPE', 'Validation provinciale', null, 3, 50, 'DREN', 'AVANT_SESSION', 'CEPE_DREN_VALIDATION', 'dren_validation', self::SOURCE . ' — Article 5');
        $this->activity('CEPE', 'Testing provincial', null, 2, 60, 'DREN', 'AVANT_SESSION', 'CEPE_DREN_TESTING', 'dren_testing', self::SOURCE . ' — Article 5');
        $this->activity('CEPE', 'Suivi et contrôle', null, 15, 70, 'DREN', 'PENDANT_SESSION', 'CEPE_DREN_SUIVI_CONTROLE', 'dren_followup', self::SOURCE . ' — Article 5');
        $this->activity('CEPE', 'Supervision session / correction / transcription', null, 6, 80, 'DREN', 'PENDANT_SESSION', 'CEPE_DREN_SUPERVISION', 'dren_supervision', self::SOURCE . ' — Article 5');

        // -----------------------------------------------------------------
        // 4. DREN — BEPC
        // -----------------------------------------------------------------
        $this->activity('BEPC', 'Organisation générale', null, 5, 10, 'DREN', 'AVANT_SESSION', 'BEPC_DREN_ORGANISATION', 'dren_organisation', self::SOURCE . ' — Article 5');
        $this->activity('BEPC', 'Sélection et élaboration des sujets', null, 5, 20, 'DREN', 'AVANT_SESSION', 'BEPC_DREN_SELECTION_ELABORATION', 'dren_selection_elaboration', self::SOURCE . ' — Article 5');
        $this->activity('BEPC', 'Suivi et contrôle', null, 15, 30, 'DREN', 'PENDANT_SESSION', 'BEPC_DREN_SUIVI_CONTROLE', 'dren_followup', self::SOURCE . ' — Article 5');
        $this->activity('BEPC', 'Supervision session / correction / transcription', null, 6, 40, 'DREN', 'PENDANT_SESSION', 'BEPC_DREN_SUPERVISION', 'dren_supervision', self::SOURCE . ' — Article 5');
        $this->activity('BEPC', 'Organisation épreuves EPS', null, 2, 50, 'DREN', 'AVANT_EPREUVES_EPS', 'BEPC_EPS_DREN_ORGANISATION', 'eps_dren_organisation', self::SOURCE . ' — Article EPS');
        $this->activity('BEPC', 'Suivi et contrôle épreuves EPS', null, 6, 60, 'DREN', 'PENDANT_EPREUVES_EPS', 'BEPC_EPS_DREN_SUIVI', 'eps_dren_monitoring', self::SOURCE . ' — Article EPS');

        // -----------------------------------------------------------------
        // 5. CISCO — CEPE
        // -----------------------------------------------------------------
        $this->activity('CEPE', 'Organisation générale', null, 5, 10, 'CISCO', 'AVANT_SESSION', 'CEPE_CISCO_ORGANISATION', 'cisco_organisation', self::SOURCE . ' — Article 6');
        $this->activity('CEPE', 'Sélection et élaboration des sujets', null, 5, 20, 'CISCO', 'AVANT_SESSION', 'CEPE_CISCO_SELECTION_ELABORATION', 'cisco_selection', self::SOURCE . ' — Article 6');
        $this->activity('CEPE', 'Suivi et contrôle', null, 15, 30, 'CISCO', 'PENDANT_SESSION', 'CEPE_CISCO_SUIVI_CONTROLE', 'cisco_followup', self::SOURCE . ' — Article 6');
        $this->activity('CEPE', "Préparation des enveloppes et mise en sous-pli", null, 3, 40, 'CISCO', 'AVANT_SESSION', 'CEPE_CISCO_PREP_ENVELOPPES_2026', 'cepe2026_enveloppes', self::SOURCE . ' — Article 13 (règle exceptionnelle CEPE 2026)', true, 2026);

        // -----------------------------------------------------------------
        // 6. CISCO — BEPC
        // -----------------------------------------------------------------
        $this->activity('BEPC', 'Organisation générale', null, 5, 10, 'CISCO', 'AVANT_SESSION', 'BEPC_CISCO_ORGANISATION', 'cisco_organisation', self::SOURCE . ' — Article 6');
        $this->activity('BEPC', 'Suivi et contrôle', null, 15, 20, 'CISCO', 'PENDANT_SESSION', 'BEPC_CISCO_SUIVI_CONTROLE', 'cisco_followup', self::SOURCE . ' — Article 6');
        $this->activity('BEPC', 'Organisation épreuves EPS', null, 2, 30, 'CISCO', 'AVANT_EPREUVES_EPS', 'BEPC_EPS_CISCO_ORGANISATION', 'eps_cisco_organisation', self::SOURCE . ' — Article EPS');
        $this->activity('BEPC', 'Suivi épreuves EPS', null, 6, 40, 'CISCO', 'PENDANT_EPREUVES_EPS', 'BEPC_EPS_CISCO_SUIVI', 'eps_cisco_monitoring', self::SOURCE . ' — Article EPS');
// -----------------------------------------------------------------
        // 7. CENTRE — CEPE et BEPC (calculées par règle depuis salles/candidats)
        // -----------------------------------------------------------------
        $this->activity('CEPE', 'Préparation avant session', null, 5, 10, 'CENTRE', 'AVANT_SESSION', 'CEPE_CENTRE_AVANT_SESSION', 'centre_before_session', self::SOURCE . ' — Article 7');
        $this->activity('CEPE', 'Encadrement session écrite', null, 3, 20, 'CENTRE', 'PENDANT_SESSION', 'CEPE_CENTRE_ENCADREMENT', 'centre_session_staff', self::SOURCE . ' — Article 7');
        $this->activity('CEPE', 'Surveillants de salle', null, 3, 30, 'CENTRE', 'PENDANT_SESSION', 'CEPE_CENTRE_SURVEILLANCE_SALLE', 'centre_room_supervisors', self::SOURCE . ' — Article 7');
        $this->activity('CEPE', 'Surveillants de cour', null, 3, 40, 'CENTRE', 'PENDANT_SESSION', 'CEPE_CENTRE_SURVEILLANCE_COUR', 'centre_yard_supervisors', self::SOURCE . ' — Article 7');
        $this->activity('CEPE', 'Correction des copies', null, 5, 50, 'CENTRE', 'APRES_SESSION', 'CEPE_CENTRE_CORRECTION', 'centre_correction', self::SOURCE . ' — Article 8');
        $this->activity('CEPE', 'Transcription des notes', null, 5, 60, 'CENTRE', 'APRES_SESSION', 'CEPE_CENTRE_TRANSCRIPTION', 'centre_transcription', self::SOURCE . ' — Article 8');
        $this->activity('BEPC', 'Préparation avant session', null, 5, 10, 'CENTRE', 'AVANT_SESSION', 'BEPC_CENTRE_AVANT_SESSION', 'centre_before_session', self::SOURCE . ' — Article 7');
        $this->activity('BEPC', 'Encadrement session écrite', null, 3, 20, 'CENTRE', 'PENDANT_SESSION', 'BEPC_CENTRE_ENCADREMENT', 'centre_session_staff', self::SOURCE . ' — Article 7');
        $this->activity('BEPC', 'Surveillants de salle', null, 3, 30, 'CENTRE', 'PENDANT_SESSION', 'BEPC_CENTRE_SURVEILLANCE_SALLE', 'centre_room_supervisors', self::SOURCE . ' — Article 7');
        $this->activity('BEPC', 'Surveillants de cour', null, 3, 40, 'CENTRE', 'PENDANT_SESSION', 'BEPC_CENTRE_SURVEILLANCE_COUR', 'centre_yard_supervisors', self::SOURCE . ' — Article 7');
        $this->activity('BEPC', 'Correction des copies', null, 5, 50, 'CENTRE', 'APRES_SESSION', 'BEPC_CENTRE_CORRECTION', 'centre_correction', self::SOURCE . ' — Article 8');
        $this->activity('BEPC', 'Transcription des notes', null, 5, 60, 'CENTRE', 'APRES_SESSION', 'BEPC_CENTRE_TRANSCRIPTION', 'centre_transcription', self::SOURCE . ' — Article 8');

        // -----------------------------------------------------------------
        // 8. EPS / GYM — BEPC (niveau centre/GYM)
        // -----------------------------------------------------------------
        $this->activity('BEPC', 'Préparation avant épreuves EPS', null, 4, 10, 'EPS', 'AVANT_EPREUVES_EPS', 'BEPC_EPS_AVANT', 'eps_before', self::SOURCE . ' — Article EPS');
        $this->activity('BEPC', 'Déroulement épreuves EPS', null, 4, 20, 'EPS', 'PENDANT_EPREUVES_EPS', 'BEPC_EPS_PENDANT', 'eps_during', self::SOURCE . ' — Article EPS');
        $this->activity('BEPC', 'Après épreuves EPS', null, 4, 30, 'EPS', 'APRES_EPREUVES_EPS', 'BEPC_EPS_APRES', 'eps_after', self::SOURCE . ' — Article EPS');
    }
/**
     * Insert a single decree activity if its activity_code is not already present.
     */
    private function activity(
        string $examen,
        string $libelle,
        ?int $maxAgents,
        int $nbJours,
        int $ordre,
        string $level,
        string $phase,
        string $activityCode,
        ?string $ruleKey,
        string $sourceRule,
        bool $isSpecialRule = false,
        ?int $applicableYear = null
    ): void {
        $exists = DB::table('vacation_2026_activities')
            ->where('activity_code', $activityCode)
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('vacation_2026_activities')->insert([
            'examen' => $examen,
            'libelle' => $libelle,
            'max_agents' => $maxAgents,
            'nb_jours' => $nbJours,
            'ordre' => $ordre,
            'level' => $level,
            'phase' => $phase,
            'year' => '2026',
            'activity_code' => $activityCode,
            'rule_key' => $ruleKey,
            'source_rule' => $sourceRule,
            'is_special_rule' => $isSpecialRule,
            'applicable_year' => $applicableYear,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Kept intentionally empty: seeds must never delete user data on rollback.
    }
};
