<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Fix CEPE/BEPC activity labels and add CAP activities with 4-type groups.
 * Only these 3 activities should have OG, SUP, COORDO, SOUS PLI groups:
 * 1. Travaux de préparation des sujets pour le CEPE
 * 2. Préparation des sujets pour le BEPC
 * 3. Impression et sous-pli pour le CAP
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('vacation_2026_activities')) {
            return;
        }

        $source = 'Décret N°2026-1257 du 18 mai 2026';

        // ========================
        // 1. FIX CEPE LABEL
        // ========================
        DB::table('vacation_2026_activities')
            ->where('activity_code', 'CEPE_CENTRAL_PREP_SUJETS')
            ->update(['libelle' => 'Travaux de préparation des sujets pour le CEPE']);

        // ========================
        // 2. FIX BEPC LABEL
        // ========================
        DB::table('vacation_2026_activities')
            ->where('activity_code', 'BEPC_CENTRAL_PREP_SUJETS')
            ->update(['libelle' => 'Préparation des sujets pour le BEPC']);

        // ========================
        // 3. ADD CAP ACTIVITY
        // ========================
        $capExists = DB::table('vacation_2026_activities')
            ->where('activity_code', 'CAP_CENTRAL_IMPRESSION_SOUS_PLI')
            ->exists();

        if (!$capExists) {
            DB::table('vacation_2026_activities')->insert([
                'examen' => 'CAP',
                'libelle' => 'Impression et sous-pli pour le CAP',
                'max_agents' => 40,
                'nb_jours' => 5,
                'ordre' => 1,
                'level' => 'CENTRAL',
                'phase' => 'AVANT_SESSION',
                'year' => '2026',
                'activity_code' => 'CAP_CENTRAL_IMPRESSION_SOUS_PLI',
                'rule_key' => null,
                'source_rule' => $source . ' — Article CAP',
                'is_special_rule' => false,
                'applicable_year' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Revert CEPE label
        DB::table('vacation_2026_activities')
            ->where('activity_code', 'CEPE_CENTRAL_PREP_SUJETS')
            ->update(['libelle' => 'Travaux de préparation des sujets']);

        // Revert BEPC label
        DB::table('vacation_2026_activities')
            ->where('activity_code', 'BEPC_CENTRAL_PREP_SUJETS')
            ->update(['libelle' => 'Préparation des sujets']);

        // Delete CAP activity (careful - only if no assignments)
        DB::table('vacation_2026_activities')
            ->where('activity_code', 'CAP_CENTRAL_IMPRESSION_SOUS_PLI')
            ->delete();
    }
};
