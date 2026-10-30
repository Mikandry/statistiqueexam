<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Nombre de colis remis à un centre d'écrit pour une année : base de l'arrêtage par point de largage.
        Schema::create('dispatching_parcels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('centre_ecrit_id')->constrained('centre_ecrits')->cascadeOnDelete();
            $table->string('annee', 9);
            $table->unsignedInteger('colis')->default(0);
            $table->foreignId('saisi_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['centre_ecrit_id', 'annee']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispatching_parcels');
    }
};
