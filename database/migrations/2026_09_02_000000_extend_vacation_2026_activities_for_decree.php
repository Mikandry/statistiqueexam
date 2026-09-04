<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extends the existing vacation_2026_activities table so that it can act as
 * the database-driven rule catalogue for Décret N°2026-1257.
 *
 * The existing columns (examen, libelle, max_agents, nb_jours, taux_activite, ordre)
 * are preserved and reused: max_agents = "required personnel" for fixed-staff
 * (CENTRAL) activities, nb_jours = default duration.
 *
 * New columns describe the administrative level, phase, a stable activity code,
 * the calculation rule key (for computed activities at DREN/CISCO/CENTRE/EPS level)
 * and a flag for year-specific exceptional rules (e.g. CEPE 2026 Article 13).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('vacation_2026_activities')) {
            return;
        }

        Schema::table('vacation_2026_activities', function (Blueprint $table) {
            if (! Schema::hasColumn('vacation_2026_activities', 'level')) {
                $table->string('level', 20)->default('CENTRAL')->after('examen');
            }
            if (! Schema::hasColumn('vacation_2026_activities', 'phase')) {
                $table->string('phase', 40)->nullable()->after('level');
            }
            if (! Schema::hasColumn('vacation_2026_activities', 'year')) {
                $table->string('year', 9)->nullable()->default('2026')->after('phase');
            }
            if (! Schema::hasColumn('vacation_2026_activities', 'activity_code')) {
                $table->string('activity_code', 120)->nullable()->after('libelle');
            }
            if (! Schema::hasColumn('vacation_2026_activities', 'rule_key')) {
                $table->string('rule_key', 120)->nullable()->after('activity_code');
            }
            if (! Schema::hasColumn('vacation_2026_activities', 'source_rule')) {
                $table->string('source_rule', 120)->nullable()->after('rule_key');
            }
            if (! Schema::hasColumn('vacation_2026_activities', 'is_special_rule')) {
                $table->boolean('is_special_rule')->default(false)->after('source_rule');
            }
            if (! Schema::hasColumn('vacation_2026_activities', 'applicable_year')) {
                $table->unsignedInteger('applicable_year')->nullable()->after('is_special_rule');
            }
        });

        if (! $this->indexExists('vacation_2026_activities', 'vacation_2026_activities_activity_code_unique')) {
            Schema::table('vacation_2026_activities', function (Blueprint $table) {
                $table->unique('activity_code', 'vacation_2026_activities_activity_code_unique');
            });
        }

        if (! $this->indexExists('vacation_2026_activities', 'vacation_2026_activities_level_index')) {
            Schema::table('vacation_2026_activities', function (Blueprint $table) {
                $table->index('level', 'vacation_2026_activities_level_index');
            });
        }

        // max_agents = required personnel for fixed-staff (CENTRAL) activities.
        // For computed activities (DREN/CISCO/CENTRE/EPS) the quota is derived
        // by the rule engine, so the column must accept NULL.
        if (Schema::hasColumn('vacation_2026_activities', 'max_agents')) {
            Schema::table('vacation_2026_activities', function (Blueprint $table) {
                $table->unsignedInteger('max_agents')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('vacation_2026_activities')) {
            return;
        }

        Schema::table('vacation_2026_activities', function (Blueprint $table) {
            $table->dropColumn([
                'level',
                'phase',
                'year',
                'activity_code',
                'rule_key',
                'source_rule',
                'is_special_rule',
                'applicable_year',
            ]);
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        try {
            return Schema::hasIndex($table, $index);
        } catch (\Throwable) {
            return false;
        }
    }
};