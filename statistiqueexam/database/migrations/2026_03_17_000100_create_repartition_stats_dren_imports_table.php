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
        Schema::create('repartition_stats_dren_imports', function (Blueprint $table) {
            $table->id();
            $table->string('annee', 9);
            $table->string('dren', 120);
            $table->unsignedInteger('total_candidats')->default(0);
            $table->unsignedInteger('total_salles')->default(0);
            $table->unsignedInteger('anglais')->default(0);
            $table->unsignedInteger('espagnol')->default(0);
            $table->unsignedInteger('allemand')->default(0);
            $table->unsignedInteger('option_b')->default(0);
            $table->timestamps();

            $table->unique(['annee', 'dren'], 'repartition_stats_dren_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repartition_stats_dren_imports');
    }
};
