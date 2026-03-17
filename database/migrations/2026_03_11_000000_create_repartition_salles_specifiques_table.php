<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repartition_salles_specifiques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('centre_ecrit_id')->constrained()->cascadeOnDelete();
            $table->string('annee', 9);
            $table->string('type_examen', 10);
            $table->unsignedInteger('numero_salle');
            $table->string('type_handicap');
            $table->string('saisi_par')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repartition_salles_specifiques');
    }
};
