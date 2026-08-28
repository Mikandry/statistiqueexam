<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_agents', fn (Blueprint $table) => $table->string('indice')->nullable()->after('grade'));
        Schema::create('hr_document_settings', function (Blueprint $table) {
            $table->id();
            $table->string('ministere')->nullable();
            $table->string('secretariat_general')->nullable();
            $table->string('direction_generale')->nullable();
            $table->string('direction')->nullable();
            $table->string('service')->nullable();
            $table->string('reference_prefix')->default('N°');
            $table->unsignedInteger('next_reference_number')->default(1);
            $table->unsignedSmallInteger('reference_year')->nullable();
            $table->string('signataire')->nullable();
            $table->timestamps();
        });
        DB::table('hr_document_settings')->insert(['ministere' => "MINISTERE DE L'EDUCATION NATIONALE", 'service' => 'Service des Ressources Humaines', 'reference_year' => now()->year, 'created_at' => now(), 'updated_at' => now()]);
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_document_settings');
        Schema::table('hr_agents', fn (Blueprint $table) => $table->dropColumn('indice'));
    }
};