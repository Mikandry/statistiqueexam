<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cap_cae_result_batches', function (Blueprint $table) {
            $table->id();
            $table->string('exam_type', 3);
            $table->unsignedSmallInteger('year');
            $table->string('source_filename');
            $table->unsignedInteger('total_candidates')->default(0);
            $table->unsignedInteger('total_centres')->default(0);
            $table->json('institution_lines')->nullable();
            $table->string('signer_function')->nullable();
            $table->string('signer_name')->nullable();
            $table->string('signer_place')->nullable();
            $table->date('signature_date')->nullable();
            $table->json('anomalies')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('cap_cae_result_candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('cap_cae_result_batches')->cascadeOnDelete();
            $table->string('source_number')->nullable();
            $table->string('name');
            $table->date('birth_date')->nullable();
            $table->string('service_location')->nullable();
            $table->string('dren');
            $table->string('centre');
            $table->unsignedInteger('general_order');
            $table->unsignedInteger('centre_order');
            $table->unsignedInteger('source_row');
            $table->json('source_data')->nullable();
            $table->timestamps();

            $table->index(['batch_id', 'centre']);
            $table->unique(['batch_id', 'general_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cap_cae_result_candidates');
        Schema::dropIfExists('cap_cae_result_batches');
    }
};
