<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Seed activity groups ONLY for these 3 CENTRAL activities:
 * 1. CEPE_CENTRAL_PREP_SUJETS (Travaux de préparation des sujets pour le CEPE)
 * 2. BEPC_CENTRAL_PREP_SUJETS (Préparation des sujets pour le BEPC)
 * 3. CAP_CENTRAL_IMPRESSION_SOUS_PLI (Impression et sous-pli pour le CAP)
 *
 * Each gets exactly 4 groups: OG, SUP, COORDO, SOUS PLI
 * Personnel is evenly distributed across the 4 groups.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('vacation_2026_activity_groups')) {
            return;
        }

        // Target activity codes (and only these)
        $targetCodes = [
            'CEPE_CENTRAL_PREP_SUJETS',
            'BEPC_CENTRAL_PREP_SUJETS',
            'CAP_CENTRAL_IMPRESSION_SOUS_PLI',
        ];

        $activities = DB::table('vacation_2026_activities')
            ->whereIn('activity_code', $targetCodes)
            ->get();

        $groups = ['OG', 'SUP', 'COORDO', 'SOUS PLI'];

        foreach ($activities as $activity) {
            $base = (int) ($activity->max_agents ?? 0);

            foreach ($groups as $index => $groupe) {
                // Distribute personnel evenly across 4 groups
                $personnel = intdiv($base, 4) + ($index < ($base % 4) ? 1 : 0);

                DB::table('vacation_2026_activity_groups')->updateOrInsert(
                    ['activity_id' => $activity->id, 'groupe' => $groupe],
                    [
                        'personnel' => $personnel,
                        'nb_jours' => (int) $activity->nb_jours,
                        'taux' => (float) ($activity->taux_activite ?? 0),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        // ============================================================
        // CLEANUP: Remove all groups that don't belong to target codes
        // ============================================================
        $targetActivityIds = DB::table('vacation_2026_activities')
            ->whereIn('activity_code', $targetCodes)
            ->pluck('id')
            ->toArray();

        DB::table('vacation_2026_activity_groups')
            ->whereNotIn('activity_id', $targetActivityIds)
            ->delete();
    }

    public function down(): void
    {
        // On rollback, delete all groups for the target activities
        $targetCodes = [
            'CEPE_CENTRAL_PREP_SUJETS',
            'BEPC_CENTRAL_PREP_SUJETS',
            'CAP_CENTRAL_IMPRESSION_SOUS_PLI',
        ];

        $targetActivityIds = DB::table('vacation_2026_activities')
            ->whereIn('activity_code', $targetCodes)
            ->pluck('id')
            ->toArray();

        DB::table('vacation_2026_activity_groups')
            ->whereIn('activity_id', $targetActivityIds)
            ->delete();
    }
};
