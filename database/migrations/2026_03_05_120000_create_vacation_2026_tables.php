<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vacation_2026_agents', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('im')->nullable()->index();
            $table->string('localite_service');
            $table->string('cin')->nullable();
            $table->timestamps();
        });

        Schema::create('vacation_2026_activities', function (Blueprint $table) {
            $table->id();
            $table->string('examen');
            $table->string('libelle');
            $table->unsignedInteger('max_agents');
            $table->unsignedInteger('nb_jours');
            $table->unsignedInteger('ordre')->default(0);
            $table->timestamps();
        });

        Schema::create('vacation_2026_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('vacation_2026_agents')->cascadeOnDelete();
            $table->foreignId('activity_id')->constrained('vacation_2026_activities')->cascadeOnDelete();
            $table->decimal('taux', 12, 2)->nullable();
            $table->timestamps();

            $table->unique('agent_id');
        });

        Schema::create('vacation_2026_settings', function (Blueprint $table) {
            $table->id();
            $table->text('entete')->nullable();
            $table->text('considerant')->nullable();
            $table->string('signature')->nullable();
            $table->timestamps();
        });

        DB::table('vacation_2026_activities')->insert([
            ['examen' => 'CEPE', 'libelle' => 'Finalisation des sujets', 'max_agents' => 25, 'nb_jours' => 10, 'ordre' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['examen' => 'CEPE', 'libelle' => 'Validation des sujets', 'max_agents' => 31, 'nb_jours' => 5, 'ordre' => 20, 'created_at' => now(), 'updated_at' => now()],
            ['examen' => 'CEPE', 'libelle' => 'Choix des sujets', 'max_agents' => 26, 'nb_jours' => 3, 'ordre' => 30, 'created_at' => now(), 'updated_at' => now()],
            ['examen' => 'CEPE', 'libelle' => 'Traitement informatique et publication', 'max_agents' => 42, 'nb_jours' => 8, 'ordre' => 40, 'created_at' => now(), 'updated_at' => now()],
            ['examen' => 'BEPC', 'libelle' => 'Sélection régionale des sujets', 'max_agents' => 50, 'nb_jours' => 5, 'ordre' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['examen' => 'BEPC', 'libelle' => 'Sélection nationale des sujets', 'max_agents' => 125, 'nb_jours' => 5, 'ordre' => 20, 'created_at' => now(), 'updated_at' => now()],
            ['examen' => 'BEPC', 'libelle' => 'Préparation et dispatching', 'max_agents' => 80, 'nb_jours' => 10, 'ordre' => 30, 'created_at' => now(), 'updated_at' => now()],
            ['examen' => 'BEPC', 'libelle' => 'Traitement informatique et publication', 'max_agents' => 42, 'nb_jours' => 15, 'ordre' => 40, 'created_at' => now(), 'updated_at' => now()],
            ['examen' => 'CAP', 'libelle' => 'Choix des sujets', 'max_agents' => 50, 'nb_jours' => 5, 'ordre' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['examen' => 'CAP', 'libelle' => 'Préparation enveloppes', 'max_agents' => 100, 'nb_jours' => 10, 'ordre' => 20, 'created_at' => now(), 'updated_at' => now()],
            ['examen' => 'CAP', 'libelle' => 'Impression et sous-pli', 'max_agents' => 100, 'nb_jours' => 10, 'ordre' => 30, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('vacation_2026_settings')->insert([
            'entete' => "REPOBLIKAN'I MADAGASIKARA\nFitiavana - Tanindrazana - Fandrosoana\nMINISTERE DE L'EDUCATION NATIONALE\nService de l'Organisation des Examens",
            'considerant' => "Vu le décret n°2025-235 relatif aux vacations d'examens.\nLes agents listés ci-dessous sont retenus pour les activités au niveau central.",
            'signature' => 'Le Directeur des Examens Nationaux',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('vacation_2026_assignments');
        Schema::dropIfExists('vacation_2026_settings');
        Schema::dropIfExists('vacation_2026_activities');
        Schema::dropIfExists('vacation_2026_agents');
    }
};
