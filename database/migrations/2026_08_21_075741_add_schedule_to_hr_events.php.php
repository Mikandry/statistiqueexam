<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_events', function (Blueprint $table) {
            $table->time('heure_debut')->nullable()->after('date_fin');
            $table->time('heure_fin')->nullable()->after('heure_debut');
            $table->decimal('duree_heures', 5, 2)->nullable()->after('heure_fin');
            $table->json('jours_semaine')->nullable()->after('duree_heures');
            $table->string('lieu')->nullable()->after('jours_semaine');
            $table->string('organisme')->nullable()->after('lieu');
            $table->string('destination')->nullable()->after('organisme');
        });
    }

    public function down(): void
    {
        Schema::table('hr_events', function (Blueprint $table) {
            $table->dropColumn([
                'heure_debut',
                'heure_fin',
                'duree_heures',
                'jours_semaine',
                'lieu',
                'organisme',
                'destination',
            ]);
        });
    }
};