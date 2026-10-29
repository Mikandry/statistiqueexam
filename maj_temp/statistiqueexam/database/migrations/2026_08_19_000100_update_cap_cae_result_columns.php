<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cap_cae_result_batches', function (Blueprint $table) {
            $table->string('list_status', 20)->default('definitive')->after('exam_type');
            $table->date('pv_date')->nullable()->after('signature_date');
        });

        Schema::table('cap_cae_result_candidates', function (Blueprint $table) {
            $table->string('registration_number')->nullable()->after('source_number');
            $table->renameColumn('service_location', 'birth_place');
        });
    }

    public function down(): void
    {
        Schema::table('cap_cae_result_candidates', function (Blueprint $table) {
            $table->renameColumn('birth_place', 'service_location');
            $table->dropColumn('registration_number');
        });

        Schema::table('cap_cae_result_batches', function (Blueprint $table) {
            $table->dropColumn(['list_status', 'pv_date']);
        });
    }
};