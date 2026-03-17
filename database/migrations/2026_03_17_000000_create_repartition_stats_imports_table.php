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
        Schema::create('repartition_stats_imports', function (Blueprint $table) {
            $table->id();
            $table->string('annee', 9);
            $table->string('type_examen', 10);
            $table->string('dren', 120);
            $table->string('cisco', 120);
            $table->string('centre_correction', 160);
            $table->string('centre_ecrit', 160);
            $table->string('centre_key', 64);
            $table->unsignedInteger('total_salles')->default(0);
            $table->unsignedInteger('total_candidats')->default(0);
            $table->timestamps();

            $table->unique(['annee', 'type_examen', 'centre_key'], 'repartition_stats_imports_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repartition_stats_imports');
    }
};
