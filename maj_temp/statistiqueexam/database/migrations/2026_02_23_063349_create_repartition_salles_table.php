<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('repartition_salles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('centre_ecrit_id')->constrained()->cascadeOnDelete();
            $table->string('annee', 9);
            $table->string('langue');
            $table->unsignedInteger('numero_salle');
            $table->unsignedInteger('effectif')->default(0);
            $table->timestamps();

            $table->unique(['centre_ecrit_id', 'annee', 'langue', 'numero_salle'], 'repartition_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repartition_salles');
    }
};
