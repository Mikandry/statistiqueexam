<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('repartition_stats_dren_imports')) {
            Schema::table('repartition_stats_dren_imports', function (Blueprint $table) {
                if (! Schema::hasColumn('repartition_stats_dren_imports', 'cisco')) {
                    $table->string('cisco', 120)->nullable()->after('annee');
                }
            });

            // copy existing dren values into cisco column when present
            DB::table('repartition_stats_dren_imports')->whereNotNull('dren')->update(['cisco' => DB::raw('dren')]);

            Schema::table('repartition_stats_dren_imports', function (Blueprint $table) {
                if (Schema::hasColumn('repartition_stats_dren_imports', 'dren')) {
                    try {
                        $table->dropUnique('repartition_stats_dren_unique');
                    } catch (\Exception $e) {
                        // ignore if index does not exist or has already been dropped
                    }
                    $table->dropColumn('dren');
                }
            });

            Schema::table('repartition_stats_dren_imports', function (Blueprint $table) {
                try {
                    $table->unique(['annee', 'cisco'], 'repartition_stats_dren_unique');
                } catch (\Exception $e) {
                    // ignore if index exists
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('repartition_stats_dren_imports')) {
            Schema::table('repartition_stats_dren_imports', function (Blueprint $table) {
                if (! Schema::hasColumn('repartition_stats_dren_imports', 'dren')) {
                    $table->string('dren', 120)->nullable()->after('annee');
                }
            });

            // copy back cisco -> dren
            DB::table('repartition_stats_dren_imports')->whereNotNull('cisco')->update(['dren' => DB::raw('cisco')]);

            Schema::table('repartition_stats_dren_imports', function (Blueprint $table) {
                if (Schema::hasColumn('repartition_stats_dren_imports', 'cisco')) {
                    try {
                        $table->dropUnique('repartition_stats_dren_unique');
                    } catch (\Exception $e) {
                        // ignore if index does not exist or has already been dropped
                    }
                    $table->dropColumn('cisco');
                }
            });

            Schema::table('repartition_stats_dren_imports', function (Blueprint $table) {
                try {
                    $table->unique(['annee', 'dren'], 'repartition_stats_dren_unique');
                } catch (\Exception $e) {
                    // ignore if index exists
                }
            });
        }
    }
};
