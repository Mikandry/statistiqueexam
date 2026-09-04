<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('repartition_salles')) {
            return;
        }

        Schema::table('repartition_salles', function (Blueprint $table) {
            if (! Schema::hasColumn('repartition_salles', 'has_special_needs_candidates')) {
                $table->boolean('has_special_needs_candidates')->default(false)->after('effectif');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('repartition_salles')) {
            return;
        }

        Schema::table('repartition_salles', function (Blueprint $table) {
            $table->dropColumn('has_special_needs_candidates');
        });
    }
};
