<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vacation_2026_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('examen', 30);
            $table->unsignedSmallInteger('annee');
            $table->string('libelle');
            $table->date('date_session');
            $table->string('level', 20)->default('CENTRAL');
            $table->timestamps();
            $table->unique(['examen', 'annee', 'level']);
        });

        Schema::create('vacation_2026_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('vacation_2026_sessions')->cascadeOnDelete();
            $table->foreignId('activity_id')->constrained('vacation_2026_activities')->cascadeOnDelete();
            $table->unsignedInteger('ordre')->default(0);
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->unsignedInteger('duree_jours')->default(1);
            $table->boolean('chevauchement_autorise')->default(false);
            $table->json('dependency_ids')->nullable();
            $table->boolean('manuel')->default(false);
            $table->string('statut', 30)->default('PREVISIONNEL');
            $table->timestamps();
            $table->unique(['session_id', 'activity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacation_2026_plans');
        Schema::dropIfExists('vacation_2026_sessions');
    }
};
