<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vacation_2026_eps_centres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cisco_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('candidate_count')->default(0);
            $table->timestamps();
            $table->unique(['cisco_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacation_2026_eps_centres');
    }
};
