<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ciscos', function (Blueprint $table) {
            if (! Schema::hasColumn('ciscos', 'manual_eps_candidates')) {
                $table->unsignedInteger('manual_eps_candidates')->nullable()->after('nom');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ciscos', function (Blueprint $table) {
            if (Schema::hasColumn('ciscos', 'manual_eps_candidates')) {
                $table->dropColumn('manual_eps_candidates');
            }
        });
    }
};
