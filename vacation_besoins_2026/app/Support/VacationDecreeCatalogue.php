<?php

namespace App\Support;

/**
 * Catalogue du PROJET DE DÉCRET « fixant l'organisation et la rétribution des travaux
 * portant sur les examens et concours officiels » (CEPE / BEPC), transcrit en code.
 *
 * Aucune donnée n'est lue ici : chaque méthode reçoit un contexte (nombre de candidats,
 * de salles, de centres, de CISCO, ...) et retourne la liste des ACTIVITÉS avec, pour chacune,
 * les rôles mobilisés, le nombre de personnes et la durée en jours.
 *
 * Forme d'une activité :
 *   code   : identifiant stable (EXAM.NIVEAU.ACTIVITÉ)
 *   label  : libellé de l'annexe
 *   level  : MEN_CENTRAL | DREN | CISCO | CENTRE_ECRIT | CENTRE_CORRECTION | TRANSCRIPTION | EPS
 *   phase  : AVANT_SESSION | PENDANT_SESSION | APRES_SESSION | PENDANT_APRES_SESSION |
 *            AVANT_EPS | PENDANT_EPS | APRES_EPS | SPECIALE
 *   days   : durée en jours
 *   roles  : [ ['label' => ..., 'rate' => code de taux, 'n' => effectif], ... ]
 *   rule   : règle appliquée (affichée à l'utilisateur)
 *   ref    : référence dans le décret
 *
 * Les tranches utilisent la division par excès : 1001 candidats = 2 tranches de 1000
 * (« tranche entamée = tranche entière »).
 */
final class VacationDecreeCatalogue
{
    public const EXAMS = ['CEPE', 'BEPC'];

    public const LEVELS = [
        'MEN_CENTRAL' => 'MEN central',
        'DREN' => 'DREN',
        'CISCO' => 'CISCO',
        'CENTRE_ECRIT' => "Centres d'écrit",
        'CENTRE_CORRECTION' => 'Centres de correction',
        'TRANSCRIPTION' => 'Centres de transcription',
        'EPS' => 'EPS',
    ];

    public const PHASES = [
        'AVANT_SESSION' => 'Avant session',
        'PENDANT_SESSION' => 'Pendant session',
        'APRES_SESSION' => 'Après session',
        'PENDANT_APRES_SESSION' => 'Pendant et après session',
        'AVANT_EPS' => 'Avant épreuves EPS',
        'PENDANT_EPS' => 'Pendant épreuves EPS',
        'APRES_EPS' => 'Après épreuves EPS',
        'SPECIALE' => 'Indemnité spéciale (Art. 7)',
    ];

    public const CENTRE_ECRIT = "CENTRE D'ECRIT SEULEMENT";
    public const CENTRE_CORRECTION = 'CENTRE DE CORRECTION SEULEMENT';
    public const CENTRE_JUMELES = "CENTRE D'ECRIT ET CORRECTION JUMELES";
    public const CENTRE_TRANSCRIPTION = 'CENTRE DE TRANSCRIPTION';
    public const CENTRE_SOUS = 'SOUS-CENTRE';

    /** Matières du CEPE (7) — « un correcteur par matière », 7 coordonnateurs. */
    public const CEPE_MATIERES = 7;

    /**
     * Matières du BEPC : [clé, libellé, littéraire ?, langue de la répartition]
     * Les matières littéraires (Malagasy, Histoire-Géographie/EC) : 60 copies/jour ; les autres : 80.
     */
    public const BEPC_MATIERES = [
        ['malagasy', 'Malagasy', true, null],
        ['hg', 'Histoire-Géographie / EC', true, null],
        ['francais', 'Français', false, null],
        ['maths', 'Mathématiques', false, null],
        ['svt', 'SVT', false, null],
        ['pc', 'Physique-Chimie', false, null],
        ['anglais', 'Anglais', false, 'Anglais'],
        ['allemand', 'Allemand', false, 'Allemand'],
        ['espagnol', 'Espagnol', false, 'Esp'],
    ];

    /**
     * Codes de taux. Le taux de chaque code est fixé par l'arrêté (Art. 5 et 7) et saisi
     * dans « Barèmes ». mode : JOUR = taux × agents × jours ; FORFAIT = taux × agents.
     *
     * @var array<string, array{label:string, group:string, mode:string}>
     */
    public const RATE_CODES = [
        // MEN central (un taux par type de travaux)
        'central.finalisation' => ['label' => 'Finalisation des sujets', 'group' => 'MEN central', 'mode' => 'JOUR'],
        'central.validation' => ['label' => 'Validation des sujets', 'group' => 'MEN central', 'mode' => 'JOUR'],
        'central.testing' => ['label' => 'Testing des sujets', 'group' => 'MEN central', 'mode' => 'JOUR'],
        'central.choix' => ['label' => 'Choix des sujets', 'group' => 'MEN central', 'mode' => 'JOUR'],
        'central.retouche' => ['label' => 'Retouche', 'group' => 'MEN central', 'mode' => 'JOUR'],
        'central.enveloppes' => ['label' => 'Préparation des enveloppes', 'group' => 'MEN central', 'mode' => 'JOUR'],
        'central.informatique' => ['label' => 'Travaux informatiques', 'group' => 'MEN central', 'mode' => 'JOUR'],
        'central.preparation_sujets' => ['label' => 'Préparation des sujets (impression, sous-pli, soubique)', 'group' => 'MEN central', 'mode' => 'JOUR'],
        'central.dispatching' => ['label' => 'Dispatching des sujets', 'group' => 'MEN central', 'mode' => 'JOUR'],
        'central.traitement' => ['label' => 'Traitement des données et publication des résultats', 'group' => 'MEN central', 'mode' => 'JOUR'],
        'central.supervision' => ['label' => 'Supervision session / correction / transcription', 'group' => 'MEN central', 'mode' => 'JOUR'],
        'central.repartition_feuilles' => ['label' => "Répartition et envoi des feuilles d'examen (BEPC)", 'group' => 'MEN central', 'mode' => 'JOUR'],
        'central.supervision_selection' => ['label' => 'Supervision sélection et élaboration régionale (BEPC)', 'group' => 'MEN central', 'mode' => 'JOUR'],
        'central.selection_nationale' => ['label' => 'Sélection et élaboration nationale (BEPC)', 'group' => 'MEN central', 'mode' => 'JOUR'],
        'central.livres_enveloppes' => ['label' => 'Préparation des livres et enveloppes (BEPC)', 'group' => 'MEN central', 'mode' => 'JOUR'],

        // DREN
        'dren.directeur' => ['label' => 'Directeur régional (président)', 'group' => 'DREN', 'mode' => 'JOUR'],
        'dren.chef_service' => ['label' => 'Chef de service des examens (vice-président)', 'group' => 'DREN', 'mode' => 'JOUR'],
        'dren.resp_informatique' => ['label' => 'Responsable informatique DREN', 'group' => 'DREN', 'mode' => 'JOUR'],
        'dren.agent' => ['label' => 'Agent DREN', 'group' => 'DREN', 'mode' => 'JOUR'],
        'dren.agent_elaboration' => ['label' => 'Agent / élaborateur de sujets (DREN)', 'group' => 'DREN', 'mode' => 'JOUR'],
        'dren.conseiller_pedagogique' => ['label' => 'Conseiller pédagogique (provincial)', 'group' => 'DREN', 'mode' => 'JOUR'],
        'dren.enseignant' => ['label' => 'Enseignant (provincial)', 'group' => 'DREN', 'mode' => 'JOUR'],

        // CISCO
        'cisco.chef' => ['label' => 'Chef CISCO (président)', 'group' => 'CISCO', 'mode' => 'JOUR'],
        'cisco.chef_division' => ['label' => 'Chef de division des examens (vice-président)', 'group' => 'CISCO', 'mode' => 'JOUR'],
        'cisco.ati' => ['label' => 'Assistant technique en informatique', 'group' => 'CISCO', 'mode' => 'JOUR'],
        'cisco.agent' => ['label' => 'Agent CISCO', 'group' => 'CISCO', 'mode' => 'JOUR'],
        'cisco.enseignant' => ['label' => 'Enseignant (élaboration CISCO)', 'group' => 'CISCO', 'mode' => 'JOUR'],
        'cisco.conseiller_pedagogique' => ['label' => 'Conseiller pédagogique (CISCO)', 'group' => 'CISCO', 'mode' => 'JOUR'],
        'cisco.secretaire' => ['label' => 'Secrétaire (CISCO)', 'group' => 'CISCO', 'mode' => 'JOUR'],
        'cisco.securite' => ['label' => 'Agent de sécurité (CISCO)', 'group' => 'CISCO', 'mode' => 'JOUR'],
        'cisco.scellage' => ['label' => 'Responsable de scellage / sous-pli (Art. 12)', 'group' => 'CISCO', 'mode' => 'JOUR'],

        // Centres d'écrit / correction
        'centre.chef_centre' => ['label' => 'Chef de centre', 'group' => "Centres d'écrit et de correction", 'mode' => 'JOUR'],
        'centre.chef_adjoint' => ['label' => 'Chef de centre adjoint', 'group' => "Centres d'écrit et de correction", 'mode' => 'JOUR'],
        'centre.agent_secretariat' => ['label' => 'Agent de secrétariat', 'group' => "Centres d'écrit et de correction", 'mode' => 'JOUR'],
        'centre.comite_vigilance' => ['label' => 'Comité de vigilance', 'group' => "Centres d'écrit et de correction", 'mode' => 'JOUR'],
        'centre.secretaire_salle' => ['label' => 'Secrétaire (session écrite)', 'group' => "Centres d'écrit et de correction", 'mode' => 'JOUR'],
        'centre.resp_securite_sujets' => ['label' => 'Responsable de sécurité des sujets', 'group' => "Centres d'écrit et de correction", 'mode' => 'JOUR'],
        'centre.resp_securite' => ['label' => 'Responsable de la sécurité', 'group' => "Centres d'écrit et de correction", 'mode' => 'JOUR'],
        'centre.surveillant_salle' => ['label' => 'Surveillant de salle', 'group' => "Centres d'écrit et de correction", 'mode' => 'JOUR'],
        'centre.surveillant_cour' => ['label' => 'Surveillant de cour', 'group' => "Centres d'écrit et de correction", 'mode' => 'JOUR'],
        'centre.resp_securite_copies' => ['label' => 'Responsable de sécurité des copies', 'group' => "Centres d'écrit et de correction", 'mode' => 'JOUR'],
        'centre.gardien' => ['label' => 'Gardien', 'group' => "Centres d'écrit et de correction", 'mode' => 'JOUR'],
        'centre.coordonnateur' => ['label' => 'Coordonnateur de correction', 'group' => "Centres d'écrit et de correction", 'mode' => 'JOUR'],
        'centre.correcteur' => ['label' => 'Correcteur (CEPE ; BEPC autres matières)', 'group' => "Centres d'écrit et de correction", 'mode' => 'JOUR'],
        'centre.correcteur_litteraire' => ['label' => 'Correcteur BEPC matières littéraires', 'group' => "Centres d'écrit et de correction", 'mode' => 'JOUR'],

        // Transcription
        'transcription.chef_centre' => ['label' => 'Chef de centre de transcription', 'group' => 'Transcription', 'mode' => 'JOUR'],
        'transcription.chef_adjoint' => ['label' => 'Chef de centre adjoint', 'group' => 'Transcription', 'mode' => 'JOUR'],
        'transcription.comite_vigilance' => ['label' => 'Comité de vigilance', 'group' => 'Transcription', 'mode' => 'JOUR'],
        'transcription.resp_securite_copies' => ['label' => 'Responsable de sécurité des copies', 'group' => 'Transcription', 'mode' => 'JOUR'],
        'transcription.gardien' => ['label' => 'Gardien', 'group' => 'Transcription', 'mode' => 'JOUR'],
        'transcription.informatique' => ['label' => 'Agent en informatique', 'group' => 'Transcription', 'mode' => 'JOUR'],
        'transcription.resp_securite' => ['label' => 'Responsable de la sécurité', 'group' => 'Transcription', 'mode' => 'JOUR'],
        'transcription.saisie' => ['label' => 'Agent de saisie', 'group' => 'Transcription', 'mode' => 'JOUR'],
        'transcription.lecteur' => ['label' => 'Lecteur', 'group' => 'Transcription', 'mode' => 'JOUR'],
        'transcription.papillon' => ['label' => 'Responsable papillon', 'group' => 'Transcription', 'mode' => 'JOUR'],
        'transcription.verificateur' => ['label' => 'Vérificateur', 'group' => 'Transcription', 'mode' => 'JOUR'],
        'transcription.agrafeur' => ['label' => 'Agrafeur', 'group' => 'Transcription', 'mode' => 'JOUR'],

        // EPS (centres)
        'eps.chef_centre' => ['label' => 'Chef de centre EPS', 'group' => 'EPS', 'mode' => 'JOUR'],
        'eps.chef_adjoint' => ['label' => 'Chef de centre adjoint EPS', 'group' => 'EPS', 'mode' => 'JOUR'],
        'eps.agent_secretariat' => ['label' => 'Agent de secrétariat EPS', 'group' => 'EPS', 'mode' => 'JOUR'],
        'eps.surveillant' => ['label' => 'Surveillant EPS', 'group' => 'EPS', 'mode' => 'JOUR'],
        'eps.agent_stade' => ['label' => 'Agent de stade', 'group' => 'EPS', 'mode' => 'JOUR'],
        'eps.medecin' => ['label' => 'Médecin', 'group' => 'EPS', 'mode' => 'JOUR'],
        'eps.interrogateur' => ['label' => 'Interrogateur', 'group' => 'EPS', 'mode' => 'JOUR'],

        // Indemnité spéciale par examen (Art. 7) — forfait, jamais de taux par défaut
        'speciale.dren' => ['label' => 'Directeur régional', 'group' => 'Indemnité spéciale (Art. 7)', 'mode' => 'FORFAIT'],
        'speciale.chef_service' => ['label' => 'Chef de service des examens DREN', 'group' => 'Indemnité spéciale (Art. 7)', 'mode' => 'FORFAIT'],
        'speciale.chef_cisco' => ['label' => 'Chef CISCO', 'group' => 'Indemnité spéciale (Art. 7)', 'mode' => 'FORFAIT'],
        'speciale.chef_division' => ['label' => 'Chef de division des examens CISCO', 'group' => 'Indemnité spéciale (Art. 7)', 'mode' => 'FORFAIT'],
        'speciale.chef_centre' => ['label' => 'Chef de centre', 'group' => 'Indemnité spéciale (Art. 7)', 'mode' => 'FORFAIT'],
        'speciale.chef_adjoint' => ['label' => 'Chef de centre adjoint', 'group' => 'Indemnité spéciale (Art. 7)', 'mode' => 'FORFAIT'],
    ];

    // =====================================================================
    // Utilitaires
    // =====================================================================

    /** Division par excès : nombre de tranches entamées. */
    public static function tranches(int $value, int $per): int
    {
        if ($per <= 0 || $value <= 0) {
            return 0;
        }

        return (int) ceil($value / $per);
    }

    /** Surveillants de cour : 2 de base + 1 par tranche de 5 salles au-delà de 10 salles. */
    public static function surveillantsCour(int $salles): int
    {
        if ($salles <= 0) {
            return 0;
        }

        return 2 + self::tranches(max(0, $salles - 10), 5);
    }

    private static function role(string $label, string $rate, int $n): array
    {
        return ['label' => $label, 'rate' => $rate, 'n' => max(0, $n)];
    }

    private static function activity(
        string $exam,
        string $level,
        string $phase,
        string $slug,
        string $label,
        int $days,
        array $roles,
        string $rule,
        string $ref
    ): array {
        return [
            'code' => $exam.'.'.$level.'.'.$slug,
            'exam' => $exam,
            'level' => $level,
            'phase' => $phase,
            'label' => $label,
            'days' => $days,
            'roles' => array_values(array_filter($roles, fn ($r) => $r['n'] > 0)),
            'rule' => $rule,
            'ref' => $ref,
        ];
    }

    // =====================================================================
    // MEN CENTRAL
    // =====================================================================

    /**
     * @param  int  $provinces  nombre de provinces (« trois agents du MEN par province »)
     */
    public static function central(string $exam, int $provinces): array
    {
        $L = 'MEN_CENTRAL';
        $a = fn (string $phase, string $slug, string $label, int $days, string $rate, int $n, string $rule) => self::activity(
            $exam, $L, $phase, $slug, $label, $days,
            [self::role($rule, $rate, $n)], $rule, 'Annexe — Activités au niveau MEN central, '.$exam
        );

        if ($exam === 'CEPE') {
            $perProvince = '3 agents du MEN par province';

            return [
                $a('AVANT_SESSION', 'FINALISATION', 'Travaux de finalisation des sujets', 15, 'central.finalisation', 3 * $provinces, $perProvince),
                $a('AVANT_SESSION', 'VALIDATION', 'Travaux de validation des sujets', 5, 'central.validation', 3 * $provinces, $perProvince),
                $a('AVANT_SESSION', 'TESTING', 'Travaux de testing des sujets', 10, 'central.testing', 3 * $provinces, $perProvince),
                $a('AVANT_SESSION', 'CHOIX', 'Travaux de choix des sujets', 5, 'central.choix', 50, '50 agents y compris les agents de sécurité'),
                $a('AVANT_SESSION', 'RETOUCHE', 'Travaux de retouche', 15, 'central.retouche', 50, '50 agents y compris les agents de sécurité'),
                $a('AVANT_SESSION', 'ENVELOPPES', 'Travaux de préparation des enveloppes', 15, 'central.enveloppes', 200, '200 agents'),
                $a('AVANT_SESSION', 'INFORMATIQUE', 'Travaux informatiques', 10, 'central.informatique', 30, '30 agents'),
                $a('AVANT_SESSION', 'PREPARATION_SUJETS', 'Travaux de préparation des sujets (impression, sous-pli, soubique)', 30, 'central.preparation_sujets', 240, '240 agents y compris les agents de sécurité'),
                $a('AVANT_SESSION', 'DISPATCHING', 'Dispatching des sujets', 10, 'central.dispatching', 80, '80 agents y compris les agents de sécurité'),
                $a('PENDANT_APRES_SESSION', 'TRAITEMENT', 'Travaux de traitement des données et publication des résultats', 10, 'central.traitement', 50, '50 agents'),
                $a('PENDANT_APRES_SESSION', 'SUPERVISION', 'Supervision de la session, de la correction et de la transcription', 10, 'central.supervision', 100, '100 agents'),
            ];
        }

        return [
            $a('AVANT_SESSION', 'REPARTITION_FEUILLES', "Travaux de répartition et envoi des feuilles d'examen", 15, 'central.repartition_feuilles', 135, '135 agents'),
            $a('AVANT_SESSION', 'INFORMATIQUE', 'Travaux informatiques', 10, 'central.informatique', 30, '30 agents'),
            $a('AVANT_SESSION', 'SUPERVISION_SELECTION', 'Supervision des travaux de sélection et élaboration régionale', 10, 'central.supervision_selection', 50, '50 agents'),
            $a('AVANT_SESSION', 'SELECTION_NATIONALE', 'Travaux de sélection et élaboration des sujets au niveau national', 10, 'central.selection_nationale', 125, '125 agents y compris les agents de sécurité'),
            $a('AVANT_SESSION', 'CHOIX', 'Travaux de choix des sujets', 3, 'central.choix', 50, '50 agents y compris les agents de sécurité'),
            $a('AVANT_SESSION', 'LIVRES_ENVELOPPES', 'Travaux de préparation des livres, des enveloppes', 15, 'central.livres_enveloppes', 180, '180 agents'),
            $a('AVANT_SESSION', 'PREPARATION_SUJETS', 'Travaux de préparation des sujets (impression, sous-pli, soubique)', 30, 'central.preparation_sujets', 300, '300 agents y compris les agents de sécurité'),
            $a('AVANT_SESSION', 'DISPATCHING', 'Travaux de dispatching des sujets', 10, 'central.dispatching', 80, '80 agents y compris les agents de sécurité'),
            $a('PENDANT_APRES_SESSION', 'TRAITEMENT', 'Travaux de traitement des données et publication des résultats', 10, 'central.traitement', 50, '50 agents'),
            $a('PENDANT_APRES_SESSION', 'SUPERVISION', 'Supervision de la session, de la correction et de la transcription', 15, 'central.supervision', 150, '150 agents'),
        ];
    }

    // =====================================================================
    // DREN
    // =====================================================================

    /**
     * @param  array{centres:int, ciscos:int, chef_lieu:bool}  $ctx
     */
    public static function dren(string $exam, array $ctx): array
    {
        $L = 'DREN';
        $centres = (int) $ctx['centres'];
        $ciscos = (int) $ctx['ciscos'];
        $chefLieu = (bool) ($ctx['chef_lieu'] ?? false);
        $ref = fn (string $s) => 'Annexe — Activités au niveau DREN, '.$exam.' — '.$s;

        $president = fn () => self::role('Directeur régional (président)', 'dren.directeur', 1);
        $vice = fn () => self::role('Chef de service des examens (vice-président)', 'dren.chef_service', 1);
        $info = fn () => self::role('Responsable informatique', 'dren.resp_informatique', 1);
        $perFiveCentres = fn () => self::role('Agents DREN (1 par tranche de 5 centres d\'examen)', 'dren.agent', self::tranches($centres, 5));
        $twoPerCisco = fn () => self::role('Agents DREN (2 par CISCO)', 'dren.agent', 2 * $ciscos);

        $out = [];

        if ($exam === 'CEPE') {
            $out[] = self::activity($exam, $L, 'AVANT_SESSION', 'ORGANISATION', "Travaux d'organisation générale", 15,
                [$president(), $vice(), $info(), $perFiveCentres()],
                'Président, vice-président, 1 responsable informatique + 1 agent DREN par tranche de 5 centres', $ref('avant session'));
            $out[] = self::activity($exam, $L, 'AVANT_SESSION', 'SUPERVISION_SELECTION', 'Supervision de la sélection et de l\'élaboration des sujets au niveau CISCO', 10,
                [$president(), $vice(), $twoPerCisco()],
                'Président, vice-président + 2 agents DREN par CISCO', $ref('avant session'));
            $out[] = self::activity($exam, $L, 'AVANT_SESSION', 'SELECTION_REGIONALE', 'Sélection et élaboration des sujets au niveau régional', 10,
                [$president(), $vice(), self::role('Agents (élaboration régionale)', 'dren.agent_elaboration', 20)],
                'Président, vice-président + 20 agents', $ref('avant session'));
            $out[] = self::activity($exam, $L, 'AVANT_SESSION', 'FINALISATION_PROVINCIALE', 'Finalisation des sujets au niveau provincial (chef-lieu de province)', 10,
                array_merge([
                    self::role('Responsable des examens DREN', 'dren.agent', 1),
                    self::role('Conseiller pédagogique', 'dren.conseiller_pedagogique', 1),
                    self::role('Enseignants de 7e', 'dren.enseignant', 2),
                ], $chefLieu ? [self::role('Agents provinciaux (12 par province, chef-lieu)', 'dren.agent', 12)] : []),
                '4 agents de chaque DREN (1 resp. examens, 1 CP, 2 enseignants de 7e) + 12 agents par province (comptés à la DREN chef-lieu)',
                $ref('avant session'));
            $out[] = self::activity($exam, $L, 'AVANT_SESSION', 'VALIDATION_PROVINCIALE', 'Validation des sujets au niveau provincial (chef-lieu de province)', 5,
                array_merge([
                    self::role('Responsable des examens DREN', 'dren.agent', 1),
                    self::role('Conseiller pédagogique', 'dren.conseiller_pedagogique', 1),
                ], $chefLieu ? [self::role('Agents provinciaux (12 par province, chef-lieu)', 'dren.agent', 12)] : []),
                '2 agents de chaque DREN (1 resp. examens, 1 CP) + 12 agents par province (comptés à la DREN chef-lieu)',
                $ref('avant session'));
            $out[] = self::activity($exam, $L, 'AVANT_SESSION', 'TESTING_PROVINCIAL', 'Testing des sujets au niveau provincial (chef-lieu de province)', 5,
                array_merge([
                    self::role('Responsable des examens DREN', 'dren.agent', 1),
                    self::role('Conseiller pédagogique', 'dren.conseiller_pedagogique', 1),
                ], $chefLieu ? [
                    self::role('Agents provinciaux (12 par province, chef-lieu)', 'dren.agent', 12),
                    self::role('Enseignants (12 par province, chef-lieu)', 'dren.enseignant', 12),
                ] : []),
                '2 agents de chaque DREN + 12 agents et 12 enseignants par province (comptés à la DREN chef-lieu)',
                $ref('avant session'));
            $out[] = self::activity($exam, $L, 'PENDANT_APRES_SESSION', 'SUIVI_CONTROLE', 'Travaux de suivi et de contrôle', 15,
                [$president(), $vice(), $info(), $perFiveCentres()],
                'Président, vice-président, 1 responsable informatique + 1 agent DREN par tranche de 5 centres', $ref('pendant et après session'));
            $out[] = self::activity($exam, $L, 'PENDANT_APRES_SESSION', 'SUPERVISION', 'Supervision de la session, de la correction et de la transcription des notes', 10,
                [$twoPerCisco()], '2 agents DREN par CISCO', $ref('pendant et après session'));

            return $out;
        }

        // BEPC — épreuves écrites
        $out[] = self::activity($exam, $L, 'AVANT_SESSION', 'ORGANISATION', "Travaux d'organisation générale", 15,
            [$president(), $vice(), $info(), $perFiveCentres()],
            'Président, vice-président, 1 responsable informatique + 1 agent DREN par tranche de 5 centres', $ref('écrit, avant session'));
        $out[] = self::activity($exam, $L, 'AVANT_SESSION', 'SELECTION_ELABORATION', 'Sélection et élaboration des sujets au niveau DREN', 10,
            [$president(), $vice(), self::role('Élaborateurs de sujets', 'dren.agent_elaboration', 45), self::role('Autres agents', 'dren.agent', 5)],
            'Président, vice-président + 50 agents dont 45 élaborateurs', $ref('écrit, avant session'));
        $out[] = self::activity($exam, $L, 'PENDANT_APRES_SESSION', 'SUIVI_CONTROLE', 'Travaux de suivi et de contrôle', 15,
            [$president(), $vice(), $info(), $perFiveCentres()],
            'Président, vice-président, 1 responsable informatique + 1 agent DREN par tranche de 5 centres', $ref('écrit, pendant et après session'));
        $out[] = self::activity($exam, $L, 'PENDANT_APRES_SESSION', 'SUPERVISION', 'Supervision de la session, de la correction et de la transcription des notes', 15,
            [$twoPerCisco()], '2 agents DREN par CISCO', $ref('écrit, pendant et après session'));

        // BEPC — EPS
        $out[] = self::activity($exam, 'DREN', 'AVANT_EPS', 'EPS_ORGANISATION', "Travaux d'organisation générale des épreuves d'EPS", 15,
            [$president(), $vice(), $twoPerCisco()], 'Président, vice-président + 2 agents DREN par CISCO', $ref('EPS, avant épreuves'));
        $out[] = self::activity($exam, 'DREN', 'PENDANT_EPS', 'EPS_SUIVI', "Travaux de suivi et de contrôle des épreuves d'EPS", 15,
            [$president(), $vice(), $twoPerCisco()], 'Président, vice-président + 2 agents DREN par CISCO', $ref('EPS, pendant et après épreuves'));

        return $out;
    }

    // =====================================================================
    // CISCO
    // =====================================================================

    /**
     * @param  array{candidates:int, eps_candidates:int, annee:int, enveloppes_actif:bool, enveloppes_matieres:int}  $ctx
     */
    public static function cisco(string $exam, array $ctx): array
    {
        $L = 'CISCO';
        $cand = (int) $ctx['candidates'];
        $ref = fn (string $s) => 'Annexe — Activités au niveau CISCO, '.$exam.' — '.$s;

        $chef = fn () => self::role('Chef CISCO (président)', 'cisco.chef', 1);
        $div = fn () => self::role('Chef de division des examens (vice-président)', 'cisco.chef_division', 1);
        $ati = fn () => self::role('Assistant technique en informatique', 'cisco.ati', 1);
        $agentsMille = fn (int $base) => self::role('Agents CISCO (1 par tranche de 1000 candidats)', 'cisco.agent', self::tranches($base, 1000));

        $out = [];

        if ($exam === 'CEPE') {
            $out[] = self::activity($exam, $L, 'AVANT_SESSION', 'ORGANISATION', "Travaux d'organisation générale", 15,
                [$chef(), $div(), $ati(), $agentsMille($cand)],
                'Chef CISCO, chef de division, 1 ATI + 1 agent par tranche de 1000 candidats', $ref('avant session'));
            $out[] = self::activity($exam, $L, 'AVANT_SESSION', 'SELECTION_ELABORATION', 'Sélection et élaboration des sujets (au niveau CISCO)', 10,
                [
                    $chef(), $div(),
                    self::role('Enseignants', 'cisco.enseignant', 12),
                    self::role('Conseillers pédagogiques', 'cisco.conseiller_pedagogique', 3),
                    self::role('Secrétaires', 'cisco.secretaire', 2),
                    self::role('Agents de sécurité', 'cisco.securite', 3),
                ],
                'Chef CISCO, chef de division + 20 agents (12 enseignants, 3 CP, 2 secrétaires, 3 sécurités)', $ref('avant session'));

            if (! empty($ctx['enveloppes_actif']) && (int) ($ctx['annee'] ?? 0) === 2026) {
                $matieres = max(1, (int) ($ctx['enveloppes_matieres'] ?? 7));
                $out[] = self::activity($exam, $L, 'AVANT_SESSION', 'ENVELOPPES_ART12', 'Préparation des enveloppes et mise en sous-pli (dérogation Art. 12, CEPE 2026)',
                    $cand > 1600 ? 5 : 3,
                    [
                        self::role('Président (Chef CISCO)', 'cisco.chef', 1),
                        self::role('Vice-président (Chef de division des examens)', 'cisco.chef_division', 1),
                        self::role('Assistant technique en informatique', 'cisco.ati', 1),
                        self::role('Agents de sécurité', 'cisco.securite', 2),
                        self::role('Responsable de scellage', 'cisco.scellage', 1),
                        self::role("Agents (1 par tranche de 500 candidats × {$matieres} matières)", 'cisco.agent', $matieres * self::tranches($cand, 500)),
                    ],
                    '5 jours si > 1600 candidats, sinon 3 jours ; 1 agent par tranche de 500 candidats pour '.$matieres.' matières',
                    'Art. 12 — dérogation CEPE 2026 uniquement');
            }

            $out[] = self::activity($exam, $L, 'PENDANT_APRES_SESSION', 'SUIVI_CONTROLE', 'Travaux de suivi et de contrôle (avant, pendant et après session)', 15,
                [$chef(), $div(), $ati(), $agentsMille($cand)],
                'Chef CISCO, chef de division, 1 ATI + 1 agent par tranche de 1000 candidats', $ref('avant, pendant et après session'));

            return $out;
        }

        // BEPC — au niveau CISCO le décret ne prévoit que l'EPS
        $eps = (int) ($ctx['eps_candidates'] ?? 0);
        $out[] = self::activity($exam, $L, 'AVANT_EPS', 'EPS_ORGANISATION', "Travaux d'organisation générale des épreuves d'EPS", 15,
            [$chef(), $div(), $agentsMille($eps)],
            'Chef CISCO, chef de division + 1 agent par tranche de 1000 candidats (candidats EPS)', $ref('EPS, avant épreuves'));
        $out[] = self::activity($exam, $L, 'PENDANT_EPS', 'EPS_SUIVI', "Travaux de suivi et de contrôle des épreuves d'EPS", 15,
            [$chef(), $div(), $agentsMille($eps)],
            'Chef CISCO, chef de division + 1 agent par tranche de 1000 candidats (candidats EPS)', $ref('EPS, pendant et après épreuves'));

        return $out;
    }

    // =====================================================================
    // CENTRES D'EXAMEN (écrit, correction, transcription)
    // =====================================================================

    /**
     * Secrétariat avant session. Écrit seul : 5 j ; correction seule : 5 j ; jumelés : 8 j.
     *
     * @return array<int,array>
     */
    public static function centreAvantSession(string $exam, string $centreType, int $candidates): array
    {
        $level = $centreType === self::CENTRE_CORRECTION ? 'CENTRE_CORRECTION' : 'CENTRE_ECRIT';
        $days = $centreType === self::CENTRE_JUMELES ? 8 : 5;

        return [self::activity($exam, $level, 'AVANT_SESSION', 'SECRETARIAT_AVANT', 'Travaux de secrétariat avant session', $days,
            [
                self::role('Chef de centre', 'centre.chef_centre', 1),
                self::role('Chef de centre adjoint', 'centre.chef_adjoint', 1),
                self::role('Agents de secrétariat (1 par tranche de 250 candidats)', 'centre.agent_secretariat', max(1, self::tranches($candidates, 250))),
            ],
            'Chef de centre, adjoint + 1 agent de secrétariat par tranche de 250 candidats ; '.$days.' jours ('.strtolower($centreType).')',
            'Annexe — Centre d\'examen, '.$exam.' — avant session')];
    }

    /**
     * Travaux pendant la session écrite.
     *
     * @param  array{candidates:int, salles:int, special:bool, days:int, sous_centre:bool}  $ctx
     */
    public static function centrePendantSession(string $exam, array $ctx): array
    {
        $salles = (int) $ctx['salles'];
        $sous = (bool) ($ctx['sous_centre'] ?? false);
        $days = (int) $ctx['days'];
        $secretaires = $exam === 'CEPE' ? $salles : self::tranches($salles, 3);

        $roles = [];
        if (! $sous) {
            $roles[] = self::role('Chef de centre', 'centre.chef_centre', 1);
        }
        $roles[] = self::role('Chef de centre adjoint', 'centre.chef_adjoint', 1);
        $roles[] = self::role('Comité de vigilance', 'centre.comite_vigilance', 1);
        $roles[] = self::role($exam === 'CEPE' ? 'Secrétaires (1 par salle)' : 'Secrétaires (1 pour 3 salles)', 'centre.secretaire_salle', $secretaires);
        $roles[] = self::role('Responsable de sécurité des sujets', 'centre.resp_securite_sujets', 1);
        $roles[] = self::role('Responsables de la sécurité', 'centre.resp_securite', $sous ? 2 : 3);
        $roles[] = self::role('Surveillants de salle (2 par salle)', 'centre.surveillant_salle', 2 * $salles);
        if (! empty($ctx['special'])) {
            $roles[] = self::role('Surveillants supplémentaires (candidats à besoins spécifiques)', 'centre.surveillant_salle', 2);
        }
        $roles[] = self::role('Surveillants de cour (2 + 1 par tranche de 5 salles au-delà de 10)', 'centre.surveillant_cour', self::surveillantsCour($salles));

        return [self::activity($exam, 'CENTRE_ECRIT', 'PENDANT_SESSION', 'SESSION_ECRITE', 'Travaux pendant la session', $days, $roles,
            ($sous ? 'Sous-centre : adjoint, comité, secrétaires, sécurité sujets, 2 resp. sécurité' : 'Centre d\'écrit : chef, adjoint, comité, secrétaires, sécurité sujets, 3 resp. sécurité')
            .', 2 surveillants/salle (+2 si besoins spécifiques), surveillants de cour',
            'Annexe — Centre d\'examen, '.$exam.' — pendant session')];
    }

    /**
     * Travaux de correction (centre de correction seul ou jumelé).
     *
     * @param  array{candidates:int, langues:?array, anglais_pct:int}  $ctx
     *   langues : candidats par langue (BEPC, répartition détaillée) ou null (répartition TOTAL_BEPC)
     */
    public static function centreCorrection(string $exam, array $ctx): array
    {
        $cand = (int) $ctx['candidates'];
        $ref = 'Annexe — Centre d\'examen, '.$exam.' — après session, travaux de correction';
        $days = $exam === 'CEPE' ? 2 : 3;

        $secretariat = [
            self::role('Chef de centre', 'centre.chef_centre', 1),
            self::role('Chef de centre adjoint', 'centre.chef_adjoint', 1),
            self::role('Comité de vigilance', 'centre.comite_vigilance', 1),
            self::role('Responsable de sécurité des copies', 'centre.resp_securite_copies', 1),
            self::role('Gardien', 'centre.gardien', 1),
            self::role('Agents de secrétariat (1 par tranche de 200 candidats)', 'centre.agent_secretariat', self::tranches($cand, 200)),
        ];

        $out = [self::activity($exam, 'CENTRE_CORRECTION', 'APRES_SESSION', 'CORRECTION_SECRETARIAT', 'Secrétariat pendant la correction', $days, $secretariat,
            'Chef de centre, adjoint, comité de vigilance, sécurité des copies, gardien + 1 agent de secrétariat par tranche de 200 candidats', $ref)];

        if ($exam === 'CEPE') {
            $correcteursParMatiere = self::tranches($cand, 100 * 2);
            $out[] = self::activity($exam, 'CENTRE_CORRECTION', 'APRES_SESSION', 'CORRECTION_COPIES', 'Correcteurs et coordonnateurs', $days,
                [
                    self::role('Coordonnateurs (1 par matière)', 'centre.coordonnateur', $cand > 0 ? self::CEPE_MATIERES : 0),
                    self::role('Correcteurs (7 matières × copies ÷ (100 × 2))', 'centre.correcteur', $cand > 0 ? self::CEPE_MATIERES * $correcteursParMatiere : 0),
                ],
                '1 correcteur par matière corrige 100 copies/jour : copies ÷ (100 × 2) correcteurs par matière, × 7 matières ; 7 coordonnateurs',
                $ref);

            return $out;
        }

        // BEPC : coordonnateur par matière ; 60 copies/jour (littéraires) ou 80 (autres) sur 3 jours
        $langues = $ctx['langues'] ?? null;
        $pct = max(0, min(100, (int) ($ctx['anglais_pct'] ?? 100)));
        $coordonnateurs = 0;
        $litteraires = 0;
        $autres = 0;
        $detail = [];

        foreach (self::BEPC_MATIERES as [$key, $label, $litteraire, $langue]) {
            if ($langue === null) {
                $copies = $cand;
            } elseif ($langues === null) {
                // répartition simplifiée : seule la langue « anglais » est estimée
                $copies = $langue === 'Anglais' ? (int) round($cand * $pct / 100) : 0;
            } else {
                $copies = (int) ($langues[$langue] ?? 0);
            }
            if ($copies <= 0) {
                continue;
            }
            $cadence = $litteraire ? 60 : 80;
            $n = self::tranches($copies, $cadence * 3);
            $coordonnateurs++;
            $litteraire ? $litteraires += $n : $autres += $n;
            $detail[] = $label.' : '.$n;
        }

        $out[] = self::activity($exam, 'CENTRE_CORRECTION', 'APRES_SESSION', 'CORRECTION_COPIES', 'Correcteurs et coordonnateurs', $days,
            [
                self::role('Coordonnateurs (1 par matière)', 'centre.coordonnateur', $coordonnateurs),
                self::role('Correcteurs matières littéraires (60 copies/jour × 3 jours)', 'centre.correcteur_litteraire', $litteraires),
                self::role('Correcteurs autres matières (80 copies/jour × 3 jours)', 'centre.correcteur', $autres),
            ],
            'Par matière : copies ÷ (60 × 3) pour Malagasy et HG/EC, copies ÷ (80 × 3) pour les autres ; 1 coordonnateur par matière'
            .($detail ? ' ['.implode(' ; ', $detail).']' : ''),
            $ref);

        return $out;
    }

    /**
     * Secrétariat pendant la transcription (centre de transcription).
     */
    public static function centreTranscription(string $exam, int $candidates): array
    {
        $t = self::tranches($candidates, 1000);

        return [self::activity($exam, 'TRANSCRIPTION', 'APRES_SESSION', 'TRANSCRIPTION', 'Travaux de secrétariat pendant la transcription', 4,
            [
                self::role('Chef de centre', 'transcription.chef_centre', 1),
                self::role('Chef de centre adjoint', 'transcription.chef_adjoint', 1),
                self::role('Comités de vigilance', 'transcription.comite_vigilance', 2),
                self::role('Responsable de sécurité des copies', 'transcription.resp_securite_copies', 1),
                self::role('Gardien', 'transcription.gardien', 1),
                self::role('Agent en informatique', 'transcription.informatique', 1),
                self::role('Responsables des sécurités', 'transcription.resp_securite', 2),
                self::role('Agents de saisie (2 par tranche de 1000)', 'transcription.saisie', 2 * $t),
                self::role('Lecteurs (7 par tranche de 1000)', 'transcription.lecteur', 7 * $t),
                self::role('Responsables papillon (1 par tranche)', 'transcription.papillon', $t),
                self::role('Vérificateurs (1 par tranche)', 'transcription.verificateur', $t),
                self::role('Agrafeurs (1 par tranche)', 'transcription.agrafeur', $t),
            ],
            '8 agents fixes + 12 agents par tranche de 1000 candidats (tranche entamée = tranche entière)',
            'Annexe — Centre d\'examen, '.$exam.' — après session, transcription')];
    }

    // =====================================================================
    // EPS (BEPC) — centres EPS
    // =====================================================================

    public static function centreEps(int $candidates): array
    {
        $exam = 'BEPC';
        $ref = fn (string $s) => 'Annexe — Centre d\'examen, BEPC, EPS — '.$s;
        $secretariat = fn () => self::role('Agents de secrétariat (1 par tranche de 200 candidats)', 'eps.agent_secretariat', self::tranches($candidates, 200));
        $daysPendant = $candidates > 3000 ? 5 : 4;

        return [
            self::activity($exam, 'EPS', 'AVANT_EPS', 'SECRETARIAT_AVANT', 'Travaux de secrétariat avant session EPS', 4,
                [self::role('Chef de centre', 'eps.chef_centre', 1), self::role('Chef de centre adjoint', 'eps.chef_adjoint', 1), $secretariat()],
                'Chef de centre, adjoint + 1 agent de secrétariat par tranche de 200 candidats', $ref('avant les épreuves')),
            self::activity($exam, 'EPS', 'PENDANT_EPS', 'INTERROGATION', "Travaux d'interrogation pour les épreuves pratiques d'EPS", $daysPendant,
                [
                    self::role('Chef de centre', 'eps.chef_centre', 1),
                    self::role('Chef de centre adjoint', 'eps.chef_adjoint', 1),
                    self::role('Surveillants', 'eps.surveillant', 2),
                    self::role('Agents de stade', 'eps.agent_stade', 2),
                    self::role('Médecin', 'eps.medecin', 1),
                    self::role('Interrogateurs (3 par tranche de 600 candidats)', 'eps.interrogateur', 3 * self::tranches($candidates, 600)),
                ],
                '4 jours (5 si plus de 3000 candidats) ; 3 interrogateurs pour 1 à 600 candidats puis +3 par tranche de 600', $ref('pendant les épreuves')),
            self::activity($exam, 'EPS', 'APRES_EPS', 'SECRETARIAT_APRES', 'Travaux de secrétariat après les épreuves pratiques EPS', 4,
                [self::role('Chef de centre', 'eps.chef_centre', 1), self::role('Chef de centre adjoint', 'eps.chef_adjoint', 1), $secretariat()],
                'Chef de centre, adjoint + 1 agent de secrétariat par tranche de 200 candidats', $ref('après les épreuves')),
        ];
    }

    // =====================================================================
    // Indemnité spéciale par examen (Art. 7)
    // =====================================================================

    /**
     * @param  string  $kind  DREN | CISCO | CENTRE | CENTRE_SOUS | EPS
     */
    public static function speciale(string $exam, string $kind, string $level): array
    {
        $roles = match ($kind) {
            'DREN' => [self::role('Directeur régional', 'speciale.dren', 1), self::role('Chef de service des examens', 'speciale.chef_service', 1)],
            'CISCO' => [self::role('Chef CISCO', 'speciale.chef_cisco', 1), self::role('Chef de division des examens', 'speciale.chef_division', 1)],
            'CENTRE_SOUS' => [self::role('Chef de centre adjoint', 'speciale.chef_adjoint', 1)],
            default => [self::role('Chef de centre', 'speciale.chef_centre', 1), self::role('Chef de centre adjoint', 'speciale.chef_adjoint', 1)],
        };

        return self::activity($exam, $level, 'SPECIALE', 'SPECIALE_'.$kind, 'Indemnité spéciale par examen', 1, $roles,
            'Une indemnité spéciale par examen et par responsable (forfait)', 'Art. 7 — indemnité spéciale');
    }
}
