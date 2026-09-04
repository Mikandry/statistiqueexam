<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vacation_2026_activity_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained('vacation_2026_activities')->cascadeOnDelete();
            $table->string('groupe', 30);
            $table->unsignedInteger('personnel')->default(0);
            $table->unsignedInteger('nb_jours')->nullable();
            $table->decimal('taux', 12, 2)->default(0);
            $table->timestamps();
            $table->unique(['activity_id', 'groupe']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacation_2026_activity_groups');
    }
};
