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
        Schema::create('ciscos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dren_id')->constrained()->cascadeOnDelete();
            $table->string('nom');
            $table->timestamps();

            $table->unique(['dren_id', 'nom']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ciscos');
    }
};
