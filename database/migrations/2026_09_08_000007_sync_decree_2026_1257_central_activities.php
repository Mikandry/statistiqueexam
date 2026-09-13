<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Correctifs du catalogue central du décret n° 2026-1257.
 *
 * Le tableau central contient, pour chacun des examens CEPE et BEPC,
 * 9 activités avant session, 2 pendant la session et 2 après session.
 * Les codes restent stables : les affectations déjà saisies sont conservées.
 */
return new class extends Migration
{
    private const SOURCE = 'Décret N°2026-1257 du 18 mai 2026 — Article 4';

    public function up(): void
    {
        if (! Schema::hasTable('vacation_2026_activities')) {
            return;
        }

        $labels = [
            'CEPE_CENTRAL_FINALISATION' => 'Travaux de finalisation des sujets',
            'CEPE_CENTRAL_VALIDATION' => 'Travaux de validation des sujets',
            'CEPE_CENTRAL_TESTING' => 'Travaux de testing des sujets',
            'CEPE_CENTRAL_CHOIX' => 'Travaux de choix des sujets',
            'CEPE_CENTRAL_RETOUCHE' => 'Travaux de retouche',
            'CEPE_CENTRAL_PREP_ENVELOPPES' => 'Travaux de préparation des enveloppes',
            'CEPE_CENTRAL_DISPATCH' => 'Travaux de dispatching des sujets',
            'CEPE_CENTRAL_PREP_SUJETS' => 'Travaux de préparation des sujets pour le CEPE',
            'CEPE_CENTRAL_INFORMATIQUE' => 'Travaux informatiques',
            'CEPE_CENTRAL_SUPERVISION' => 'Travaux de supervision',
            'CEPE_CENTRAL_TRAITEMENT' => 'Travaux de traitement des données',
            'CEPE_CENTRAL_PUBLICATION' => 'Travaux de publication des résultats',
            'BEPC_CENTRAL_REPARTITION_FEUILLES' => "Travaux de répartition et d'envoi des feuilles d'examen",
            'BEPC_CENTRAL_SUPERVISION_SELECTION' => "Travaux de supervision de la sélection et de l'élaboration régionale",
            'BEPC_CENTRAL_SELECTION_NATIONALE' => "Travaux de sélection et d'élaboration des sujets au niveau national",
            'BEPC_CENTRAL_PREP_LIVRES' => 'Travaux de préparation des livres et des enveloppes',
            'BEPC_CENTRAL_DISPATCH' => 'Travaux de dispatching des sujets',
            'BEPC_CENTRAL_CHOIX' => 'Travaux de choix des sujets',
            'BEPC_CENTRAL_PREP_SUJETS' => 'Travaux de préparation des sujets pour le BEPC',
            'BEPC_CENTRAL_INFORMATIQUE' => 'Travaux informatiques',
            'BEPC_CENTRAL_SUPERVISION' => 'Travaux de supervision',
            'BEPC_CENTRAL_TRAITEMENT' => 'Travaux de traitement des données',
            'BEPC_CENTRAL_PUBLICATION' => 'Travaux de publication des résultats',
        ];

        foreach ($labels as $code => $libelle) {
            DB::table('vacation_2026_activities')
                ->where('activity_code', $code)
                ->update([
                    'libelle' => $libelle,
                    'source_rule' => self::SOURCE,
                    'updated_at' => now(),
                ]);
        }

        // Complète les deux libellés avant-session absents du catalogue BEPC.
        // Les paramètres sont alignés sur les lignes homologues déjà prévues
        // au tableau central ; ils restent modifiables depuis le module.
        $this->insertIfMissing('BEPC_CENTRAL_FINALISATION', 'Travaux de finalisation des sujets', 25, 10, 15);
        $this->insertIfMissing('BEPC_CENTRAL_VALIDATION', 'Travaux de validation des sujets', 31, 5, 25);
    }

    private function insertIfMissing(string $code, string $libelle, int $maxAgents, int $days, int $order): void
    {
        if (DB::table('vacation_2026_activities')->where('activity_code', $code)->exists()) {
            return;
        }

        DB::table('vacation_2026_activities')->insert([
            'examen' => 'BEPC',
            'libelle' => $libelle,
            'max_agents' => $maxAgents,
            'nb_jours' => $days,
            'ordre' => $order,
            'level' => 'CENTRAL',
            'phase' => 'AVANT_SESSION',
            'year' => '2026',
            'activity_code' => $code,
            'rule_key' => null,
            'source_rule' => self::SOURCE,
            'is_special_rule' => false,
            'applicable_year' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // The synchronisation must not erase activities or user assignments.
    }
};
