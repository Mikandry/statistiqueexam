<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('vacation_2026_activities')) {
            return;
        }

        $activityIds = DB::table('vacation_2026_activities')
            ->where('level', 'CENTRE')
            ->where('rule_key', 'centre_session_staff')
            ->pluck('id');

        if (Schema::hasTable('vacation_2026_assignments') && $activityIds->isNotEmpty()) {
            DB::table('vacation_2026_assignments')
                ->whereIn('activity_id', $activityIds)
                ->delete();
        }

        DB::table('vacation_2026_activities')
            ->where('level', 'CENTRE')
            ->where('rule_key', 'centre_session_staff')
            ->delete();
    }

    public function down(): void
    {
        // The activity is not part of the decree catalogue and is not restored.
    }
};
