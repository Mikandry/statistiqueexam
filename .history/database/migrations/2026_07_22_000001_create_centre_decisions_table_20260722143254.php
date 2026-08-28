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
        Schema::create('centre_decisions', function (Blueprint $table) {
            $table->id();
            $table->string('annee');
            $table->foreignId('centre_correction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('centre_ecrit_id')->constrained()->cascadeOnDelete();
            $table->string('type_examen');
            $table->boolean('actif')->default(true);
            $table->timestamps();

            $table->unique(['annee', 'centre_ecrit_id']);
            $table->index(['annee', 'type_examen', 'actif']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('centre_decisions');
    }
};
