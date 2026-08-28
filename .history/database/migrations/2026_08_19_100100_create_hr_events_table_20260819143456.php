<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('hr_agents')->cascadeOnDelete();
            $table->string('type', 50);
            $table->string('status', 30)->default('valide');
            $table->string('title')->nullable();
            $table->text('motif')->nullable();
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->string('reference')->nullable();
            $table->string('autorite')->nullable();
            $table->text('observation')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['agent_id', 'date_debut', 'date_fin']);
            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_events');
    }
};