<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('repartition_salles') || Schema::hasColumn('repartition_salles', 'eps_inapt_effectif')) {
            return;
        }

        Schema::table('repartition_salles', function (Blueprint $table) {
            $table->unsignedInteger('eps_inapt_effectif')->default(0)->after('effectif');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('repartition_salles') && Schema::hasColumn('repartition_salles', 'eps_inapt_effectif')) {
            Schema::table('repartition_salles', function (Blueprint $table) {
                $table->dropColumn('eps_inapt_effectif');
            });
        }
    }
};
