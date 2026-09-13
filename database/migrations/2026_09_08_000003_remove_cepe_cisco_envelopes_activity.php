<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // This exceptional Article 13 activity is no longer performed at CISCO.
        DB::table('vacation_2026_activities')
            ->where('activity_code', 'CEPE_CISCO_PREP_ENVELOPPES_2026')
            ->where('level', 'CISCO')
            ->delete();
    }

    public function down(): void
    {
        // Historical catalogue entry is intentionally not recreated here.
    }
};
