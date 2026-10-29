<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_agents', function (Blueprint $table) {
            $table->id();
            $table->string('matricule')->nullable()->unique();
            $table->string('nom');
            $table->string('prenoms')->nullable();
            $table->string('sexe', 20)->nullable();
            $table->date('date_naissance')->nullable();
            $table->string('cin')->nullable();
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->text('adresse')->nullable();
            $table->string('statut')->nullable();
            $table->string('corps')->nullable();
            $table->string('grade')->nullable();
            $table->string('categorie')->nullable();
            $table->string('echelon')->nullable();
            $table->string('fonction')->nullable();
            $table->date('date_recrutement')->nullable();
            $table->date('date_prise_service')->nullable();
            $table->string('direction')->nullable();
            $table->string('service')->nullable();
            $table->string('bureau')->nullable();
            $table->string('superieur_hierarchique')->nullable();
            $table->string('situation_administrative')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('hr_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('hr_agents')->cascadeOnDelete();
            $table->string('direction')->nullable();
            $table->string('service')->nullable();
            $table->string('bureau')->nullable();
            $table->string('fonction')->nullable();
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->string('motif')->nullable();
            $table->string('reference')->nullable();
            $table->boolean('current')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['agent_id', 'date_debut', 'date_fin']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_assignments');
        Schema::dropIfExists('hr_agents');
    }
};
