<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * DEPRECATED: Groups are now seeded only for 3 specific activities via 2026_09_04_000200.
     * This migration is kept as a no-op to avoid breaking existing migrations.
     */
    public function up(): void
    {
        // No-op: Groups are now managed by 2026_09_04_000200_seed_groups_only_for_three_activities.php
    }

    public function down(): void
    {
        // No-op
    }
};
