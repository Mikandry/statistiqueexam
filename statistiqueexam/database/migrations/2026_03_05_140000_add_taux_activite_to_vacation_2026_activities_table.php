<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vacation_2026_activities', function (Blueprint $table) {
            $table->decimal('taux_activite', 12, 2)->nullable()->after('nb_jours');
        });
    }

    public function down(): void
    {
        Schema::table('vacation_2026_activities', function (Blueprint $table) {
            $table->dropColumn('taux_activite');
        });
    }
};
