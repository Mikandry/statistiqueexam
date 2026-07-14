<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dren_id')->constrained('drens')->cascadeOnDelete();
            $table->foreignId('cisco_id')->constrained('ciscos')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->year('year')->default(2026);
            $table->string('exam_name')->default('BEPC');
            $table->unsignedInteger('total_candidates')->default(0);
            $table->unsignedInteger('absent_candidates')->nullable();
            $table->unsignedInteger('present_candidates')->default(0);
            $table->unsignedInteger('admitted_candidates')->nullable();
            $table->decimal('admission_threshold', 5, 2)->nullable();
            $table->decimal('success_rate', 6, 2)->default(0);
            $table->decimal('abandonment_rate', 6, 2)->default(0);
            $table->timestamp('published_at')->nullable();
            $table->string('status')->default('en_attente');
            $table->text('observations')->nullable();
            $table->timestamps();

            $table->unique(['year', 'exam_name', 'cisco_id']);
            $table->index(['status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_results');
    }
};
