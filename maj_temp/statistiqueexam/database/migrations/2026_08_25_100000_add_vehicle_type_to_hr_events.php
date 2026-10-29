<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_events', function (Blueprint $table) {
            $table->string('vehicle_type', 30)->nullable()->after('destination');
        });
    }

    public function down(): void
    {
        Schema::table('hr_events', function (Blueprint $table) {
            $table->dropColumn('vehicle_type');
        });
    }
};
