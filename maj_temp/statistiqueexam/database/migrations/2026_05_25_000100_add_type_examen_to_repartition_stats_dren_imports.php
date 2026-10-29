<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('repartition_stats_dren_imports')) {
            return;
        }

        Schema::table('repartition_stats_dren_imports', function (Blueprint $table) {
            if (! Schema::hasColumn('repartition_stats_dren_imports', 'type_examen')) {
                $table->string('type_examen', 10)->nullable()->after('annee');
            }
        });

        Schema::table('repartition_stats_dren_imports', function (Blueprint $table) {
            try {
                $table->dropUnique('repartition_stats_dren_unique');
            } catch (\Exception $e) {
                // ignore if index does not exist
            }

            if (Schema::hasColumn('repartition_stats_dren_imports', 'cisco')) {
                $table->unique(['annee', 'type_examen', 'cisco'], 'repartition_stats_dren_unique');
            } elseif (Schema::hasColumn('repartition_stats_dren_imports', 'dren')) {
                $table->unique(['annee', 'type_examen', 'dren'], 'repartition_stats_dren_unique');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('repartition_stats_dren_imports')) {
            return;
        }

        Schema::table('repartition_stats_dren_imports', function (Blueprint $table) {
            try {
                $table->dropUnique('repartition_stats_dren_unique');
            } catch (\Exception $e) {
                // ignore if index does not exist
            }

            if (Schema::hasColumn('repartition_stats_dren_imports', 'cisco')) {
                $table->unique(['annee', 'cisco'], 'repartition_stats_dren_unique');
            } elseif (Schema::hasColumn('repartition_stats_dren_imports', 'dren')) {
                $table->unique(['annee', 'dren'], 'repartition_stats_dren_unique');
            }
        });

        Schema::table('repartition_stats_dren_imports', function (Blueprint $table) {
            if (Schema::hasColumn('repartition_stats_dren_imports', 'type_examen')) {
                $table->dropColumn('type_examen');
            }
        });
    }
};
