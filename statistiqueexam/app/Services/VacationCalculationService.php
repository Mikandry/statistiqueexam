<?php

namespace App\Services;

use App\Models\Centre;
use App\Models\CentreEcrit;
use App\Models\Cisco;
use App\Models\Dren;
use App\Models\Examen;

class VacationCalculationService
{
    public const PHASE_AVANT = 'AVANT_SESSION';
    public const PHASE_PENDANT = 'PENDANT_SESSION';
    public const PHASE_APRES = 'APRES_SESSION';

    public const NIVEAU_MEN = 'MEN_CENTRAL';
    public const NIVEAU_DREN = 'DREN';
    public const NIVEAU_CISCO = 'CISCO';
    public const NIVEAU_CENTRE = 'CENTRE';
    public const NIVEAU_EPS = 'EPS_GYM';

    public const TYPE_ECRIT = 'ECRIT_SEUL';
    public const TYPE_CORRECTION = 'CORRECTION_SEULE';
    public const TYPE_JUMELE = 'JUMELE';

    /**
     * Calcule l'estimation complète des besoins et montants pour un centre donné.
     */
    public function calculateCentreVacation(Centre $centre): array
    {
        $candidats = $centre->candidats_count ?? $centre->candidats()->count();
        $salles = $centre->salles_count ?? $centre->salles()->count();
        $typeCentre = $centre->type_centre ?? self::TYPE_JUMELE;

        $activites = [];

        // --- 1. SURVEILLANCE ÉCRIT ---
        if (in_array($typeCentre, [self::TYPE_ECRIT, self::TYPE_JUMELE])) {
            // Surveillants (2 par salle + 2 si besoins spécifiques)
            $nbBesoinSpecifique = $centre->besoins_specifiques ? 2 : 0;
            $nbSurveillants = ($salles * 2) + $nbBesoinSpecifique;
            $joursEcrit = $centre->examen->duree_epreuve_jours ?? 3;
            $tauxSurveillant = $centre->examen->taux_surveillant ?? 15000;

            $activites[] = $this->formatActivite(
                'Surveillance des épreuves écrites',
                self::PHASE_PENDANT,
                self::NIVEAU_CENTRE,
                $nbSurveillants,
                $joursEcrit,
                $tauxSurveillant
            );

            // Surveillance de cour (2 jusqu'à 10 salles + 1 par tranche de 5 supplémentaires)
            $nbCour = 2;
            if ($salles > 10) {
                $nbCour += (int) ceil(($salles - 10) / 5);
            }
            $activites[] = $this->formatActivite(
                'Surveillance de cour / Sécurité centre',
                self::PHASE_PENDANT,
                self::NIVEAU_CENTRE,
                $nbCour,
                $joursEcrit,
                $centre->examen->taux_cour ?? 12000
            );

            // Secrétariat d'Écrit (ceil(candidats / 250))
            $nbSecrEcrit = (int) ceil($candidats / 250);
            $activites[] = $this->formatActivite(
                'Secrétariat de centre (Écrit)',
                self::PHASE_PENDANT,
                self::NIVEAU_CENTRE,
                max(1, $nbSecrEcrit),
                $joursEcrit + 1, // 1 jour prépa inclus (Art. 4)
                $centre->examen->taux_secretariat ?? 18000
            );
        }

        // --- 2. CORRECTION ---
        if (in_array($typeCentre, [self::TYPE_CORRECTION, self::TYPE_JUMELE])) {
            // Secrétariat Correction (ceil(candidats / 200))
            $nbSecrCorr = (int) ceil($candidats / 200);
            $joursCorr = $centre->examen->duree_correction_jours ?? 4;

            $activites[] = $this->formatActivite(
                'Secrétariat de correction & Anonymat',
                self::PHASE_APRES,
                self::NIVEAU_CENTRE,
                max(1, $nbSecrCorr),
                $joursCorr,
                $centre->examen->taux_secretariat_corr ?? 20000
            );
        }

        // --- 3. TRANSCRIPTION (Spécifique) ---
        if ($centre->nb_candidats_transcription > 0) {
            $nbTrans = (int) ceil($centre->nb_candidats_transcription / 1000) * 12;
            $activites[] = $this->formatActivite(
                'Transcription des épreuves adaptées',
                self::PHASE_PENDANT,
                self::NIVEAU_CENTRE,
                $nbTrans,
                $joursEcrit,
                $centre->examen->taux_transcription ?? 25000
            );
        }

        // Total Centre
        $montantTotalCentre = array_reduce($activites, fn($sum, $act) => $sum + $act['montant_estime'], 0);
        $totalAgentsEstimes = array_reduce($activites, fn($sum, $act) => $sum + $act['nombre_agents'], 0);

        return [
            'centre_id' => $centre->id,
            'nom_centre' => $centre->nom,
            'type_centre' => $typeCentre,
            'candidats' => $candidats,
            'salles' => $salles,
            'total_agents_estimes' => $totalAgentsEstimes,
            'montant_estime' => $montantTotalCentre,
            'activites' => $activites,
        ];
    }

    /**
     * Calcul des règles spécifiques CISCO (y compris Article 13 CEPE 2026)
     */
    public function calculateCiscoVacation(Cisco $cisco, string $codeExamen = 'CEPE'): array
    {
        $candidats = $cisco->candidats_count ?? $cisco->candidats()->count();
        $activites = [];

        // Règle CEPE 2026 - Article 13 : Préparation enveloppes & mise sous pli
        if (strtoupper($codeExamen) === 'CEPE') {
            $joursMiseSousPli = ($candidats > 1600) ? 5 : 3;
            
            // Personnel fixe: 1 Chef CISCO, 1 Chef div, 1 ATI, 2 Sécurité, 1 Resp
            $personnelFixe = 1 + 1 + 1 + 2 + 1; // 6 agents
            // 1 agent par tranche de 500 candidats pour les 7 sujets
            $agentsManiement = (int) ceil($candidats / 500);
            $totalAgentsSousPli = $personnelFixe + $agentsManiement;

            $activites[] = $this->formatActivite(
                'Mise sous-pli et préparation des enveloppes (Art. 13 CEPE 2026)',
                self::PHASE_AVANT,
                self::NIVEAU_CISCO,
                $totalAgentsSousPli,
                $joursMiseSousPli,
                20000
            );
        }

        // Règle générale CISCO (1 agent / 1000 candidats)
        $agentsGeneraux = (int) ceil($candidats / 1000);
        $activites[] = $this->formatActivite(
            'Organisation et supervision administrative CISCO',
            self::PHASE_PENDANT,
            self::NIVEAU_CISCO,
            max(2, $agentsGeneraux),
            5,
            22000
        );

        $montantTotal = array_reduce($activites, fn($sum, $act) => $sum + $act['montant_estime'], 0);

        return [
            'cisco_id' => $cisco->id,
            'nom_cisco' => $cisco->nom,
            'candidats' => $candidats,
            'montant_estime' => $montantTotal,
            'activites' => $activites,
        ];
    }

    /**
     * Calcul EPS / GYM
     */
    public function calculateEpsVacation(int $candidatsEps): array
    {
        $jours = ($candidatsEps > 3000) ? 5 : 4;
        $interrogateurs = (int) ceil($candidatsEps / 600) * 3;

        $activite = $this->formatActivite(
            'Épreuves physiques et sportives (EPS)',
            self::PHASE_PENDANT,
            self::NIVEAU_EPS,
            $interrogateurs,
            $jours,
            18000
        );

        return [
            'candidats' => $candidatsEps,
            'jours' => $jours,
            'agents' => $interrogateurs,
            'montant_estime' => $activite['montant_estime'],
            'activites' => [$activite],
        ];
    }

    /**
     * Formatage structuré d'une activité avec la formule standard.
     */
    private function formatActivite(string $libelle, string $phase, string $niveau, int $agents, int $jours, float $indemnite): array
    {
        return [
            'activite' => $libelle,
            'phase' => $phase,
            'niveau' => $niveau,
            'nombre_agents' => $agents,
            'nombre_jours' => $jours,
            'indemnite_unitaire' => $indemnite,
            'montant_estime' => $agents * $jours * $indemnite,
        ];
    }
}