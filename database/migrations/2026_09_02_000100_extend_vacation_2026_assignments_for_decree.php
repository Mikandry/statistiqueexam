<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Restructures vacation_2026_assignments so a single vacation/activity record
 * can be attached to any administrative level and scope:
 *
 *   CENTRAL  -> no scope foreign keys
 *   DREN     -> dren_id
 *   CISCO    -> cisco_id (dren_id is derivable)
 *   CENTRE   -> centre_correction_id / centre_ecrit_id / salle_id
 *   EPS      -> centre_ecrit_id (EPS/GYM centre) + role
 *
 * The old "one agent per activity" unique constraint is removed because the
 * same activity can legitimately be repeated at several scopes (e.g. the same
 * activity "Surveillants de salle" assigned per centre). Duplicate/conflict
 * controls are enforced by VacationAssignmentService (Article 5, 7, 9).
 *
 * Backward compatible: existing CENTRAL rows simply keep level='CENTRAL' and
 * NULL scope foreign keys.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('vacation_2026_assignments')) {
            return;
        }

        if ($this->indexExists('vacation_2026_assignments', 'vacation_2026_assignments_agent_id_activity_id_unique')) {
            Schema::table('vacation_2026_assignments', function (Blueprint $table) {
                $table->dropUnique('vacation_2026_assignments_agent_id_activity_id_unique');
            });
        }

        Schema::table('vacation_2026_assignments', function (Blueprint $table) {
            if (! Schema::hasColumn('vacation_2026_assignments', 'level')) {
                $table->string('level', 20)->default('CENTRAL')->after('taux');
            }
            if (! Schema::hasColumn('vacation_2026_assignments', 'phase')) {
                $table->string('phase', 40)->nullable()->after('level');
            }
            if (! Schema::hasColumn('vacation_2026_assignments', 'dren_id')) {
                $table->foreignId('dren_id')->nullable()->constrained('drens')->nullOnDelete()->after('phase');
            }
            if (! Schema::hasColumn('vacation_2026_assignments', 'cisco_id')) {
                $table->foreignId('cisco_id')->nullable()->constrained('ciscos')->nullOnDelete()->after('dren_id');
            }
            if (! Schema::hasColumn('vacation_2026_assignments', 'centre_correction_id')) {
                $table->foreignId('centre_correction_id')->nullable()->constrained('centre_corrections')->nullOnDelete()->after('cisco_id');
            }
            if (! Schema::hasColumn('vacation_2026_assignments', 'centre_ecrit_id')) {
                $table->foreignId('centre_ecrit_id')->nullable()->constrained('centre_ecrits')->nullOnDelete()->after('centre_correction_id');
            }
            if (! Schema::hasColumn('vacation_2026_assignments', 'salle_id')) {
                $table->foreignId('salle_id')->nullable()->constrained('repartition_salles')->nullOnDelete()->after('centre_ecrit_id');
            }
            if (! Schema::hasColumn('vacation_2026_assignments', 'role')) {
                $table->string('role', 160)->nullable()->after('salle_id');
            }
            if (! Schema::hasColumn('vacation_2026_assignments', 'start_date')) {
                $table->date('start_date')->nullable()->after('role');
            }
            if (! Schema::hasColumn('vacation_2026_assignments', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date');
            }
            if (! Schema::hasColumn('vacation_2026_assignments', 'nb_jours')) {
                $table->unsignedInteger('nb_jours')->nullable()->after('end_date');
            }
            if (! Schema::hasColumn('vacation_2026_assignments', 'required_personnel')) {
                $table->unsignedInteger('required_personnel')->nullable()->after('nb_jours');
            }
            if (! Schema::hasColumn('vacation_2026_assignments', 'status')) {
                $table->string('status', 20)->default('PLANIFIE')->after('required_personnel');
            }
            if (! Schema::hasColumn('vacation_2026_assignments', 'validated_at')) {
                $table->timestamp('validated_at')->nullable()->after('status');
            }
            if (! Schema::hasColumn('vacation_2026_assignments', 'notes')) {
                $table->text('notes')->nullable()->after('validated_at');
            }
        });

        $this->addIndexIfMissing('vacation_2026_assignments', ['level'], 'vacation_2026_assignments_level_index');
        $this->addIndexIfMissing('vacation_2026_assignments', ['dren_id'], 'vacation_2026_assignments_dren_id_index');
        $this->addIndexIfMissing('vacation_2026_assignments', ['cisco_id'], 'vacation_2026_assignments_cisco_id_index');
        $this->addIndexIfMissing('vacation_2026_assignments', ['centre_ecrit_id'], 'vacation_2026_assignments_centre_ecrit_id_index');
        $this->addIndexIfMissing('vacation_2026_assignments', ['salle_id'], 'vacation_2026_assignments_salle_id_index');
        $this->addIndexIfMissing('vacation_2026_assignments', ['status'], 'vacation_2026_assignments_status_index');
    }

    public function down(): void
    {
        if (! Schema::hasTable('vacation_2026_assignments')) {
            return;
        }

        Schema::table('vacation_2026_assignments', function (Blueprint $table) {
            $table->dropColumn([
                'level',
                'phase',
                'dren_id',
                'cisco_id',
                'centre_correction_id',
                'centre_ecrit_id',
                'salle_id',
                'role',
                'start_date',
                'end_date',
                'nb_jours',
                'required_personnel',
                'status',
                'validated_at',
                'notes',
            ]);
        });
    }

    private function addIndexIfMissing(string $table, array $columns, string $name): void
    {
        if ($this->indexExists($table, $name)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($columns, $name) {
            $table->index($columns, $name);
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