<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('centre_corrections', 'type_examen')) {
            Schema::table('centre_corrections', function (Blueprint $table) {
                $table->string('type_examen', 10)->default('BEPC');
            });
        }
        if ($this->indexExists('centre_corrections', 'centre_corrections_cisco_id_nom_unique')) {
            if (! $this->indexExists('centre_corrections', 'centre_corrections_cisco_id_index')) {
                Schema::table('centre_corrections', function (Blueprint $table) {
                    $table->index('cisco_id', 'centre_corrections_cisco_id_index');
                });
            }
            Schema::table('centre_corrections', function (Blueprint $table) {
                $table->dropUnique('centre_corrections_cisco_id_nom_unique');
            });
        }
        if (! $this->indexExists('centre_corrections', 'centre_corrections_cisco_nom_type_unique')) {
            Schema::table('centre_corrections', function (Blueprint $table) {
                $table->unique(['cisco_id', 'nom', 'type_examen'], 'centre_corrections_cisco_nom_type_unique');
            });
        }

        if (! Schema::hasColumn('centre_ecrits', 'type_examen')) {
            Schema::table('centre_ecrits', function (Blueprint $table) {
                $table->string('type_examen', 10)->default('BEPC');
            });
        }
        if ($this->indexExists('centre_ecrits', 'centre_ecrits_centre_correction_id_nom_unique')) {
            if (! $this->indexExists('centre_ecrits', 'centre_ecrits_centre_correction_id_index')) {
                Schema::table('centre_ecrits', function (Blueprint $table) {
                    $table->index('centre_correction_id', 'centre_ecrits_centre_correction_id_index');
                });
            }
            Schema::table('centre_ecrits', function (Blueprint $table) {
                $table->dropUnique('centre_ecrits_centre_correction_id_nom_unique');
            });
        }
        if (! $this->indexExists('centre_ecrits', 'centre_ecrits_cc_nom_type_unique')) {
            Schema::table('centre_ecrits', function (Blueprint $table) {
                $table->unique(['centre_correction_id', 'nom', 'type_examen'], 'centre_ecrits_cc_nom_type_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if ($this->indexExists('centre_ecrits', 'centre_ecrits_cc_nom_type_unique')) {
            Schema::table('centre_ecrits', function (Blueprint $table) {
                $table->dropUnique('centre_ecrits_cc_nom_type_unique');
            });
        }
        if (! $this->indexExists('centre_ecrits', 'centre_ecrits_centre_correction_id_nom_unique')) {
            Schema::table('centre_ecrits', function (Blueprint $table) {
                $table->unique(['centre_correction_id', 'nom'], 'centre_ecrits_centre_correction_id_nom_unique');
            });
        }
        if ($this->indexExists('centre_ecrits', 'centre_ecrits_centre_correction_id_index')) {
            Schema::table('centre_ecrits', function (Blueprint $table) {
                $table->dropIndex('centre_ecrits_centre_correction_id_index');
            });
        }
        if (Schema::hasColumn('centre_ecrits', 'type_examen')) {
            Schema::table('centre_ecrits', function (Blueprint $table) {
                $table->dropColumn('type_examen');
            });
        }

        if ($this->indexExists('centre_corrections', 'centre_corrections_cisco_nom_type_unique')) {
            Schema::table('centre_corrections', function (Blueprint $table) {
                $table->dropUnique('centre_corrections_cisco_nom_type_unique');
            });
        }
        if (! $this->indexExists('centre_corrections', 'centre_corrections_cisco_id_nom_unique')) {
            Schema::table('centre_corrections', function (Blueprint $table) {
                $table->unique(['cisco_id', 'nom'], 'centre_corrections_cisco_id_nom_unique');
            });
        }
        if ($this->indexExists('centre_corrections', 'centre_corrections_cisco_id_index')) {
            Schema::table('centre_corrections', function (Blueprint $table) {
                $table->dropIndex('centre_corrections_cisco_id_index');
            });
        }
        if (Schema::hasColumn('centre_corrections', 'type_examen')) {
            Schema::table('centre_corrections', function (Blueprint $table) {
                $table->dropColumn('type_examen');
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        return DB::table('information_schema.statistics')
            ->whereRaw('table_schema = DATABASE()')
            ->where('table_name', $table)
            ->where('index_name', $indexName)
            ->exists();
    }
};
