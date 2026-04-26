<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bepc_copy_distributions', function (Blueprint $table) {
            $table->id();
            $table->string('annee', 9);
            $table->foreignId('cisco_id')->constrained('ciscos')->cascadeOnDelete();
            $table->string('code_postal', 50);
            $table->timestamps();

            $table->unique(['annee', 'cisco_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bepc_copy_distributions');
    }
};
