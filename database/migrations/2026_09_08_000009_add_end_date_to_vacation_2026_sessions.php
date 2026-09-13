<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vacation_2026_sessions', function (Blueprint $table) {
            $table->date('date_fin_session')->nullable()->after('date_session');
        });
    }

    public function down(): void
    {
        Schema::table('vacation_2026_sessions', function (Blueprint $table) {
            $table->dropColumn('date_fin_session');
        });
    }
};
