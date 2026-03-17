<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vacation_2026_assignments', function (Blueprint $table) {
            $table->dropForeign(['agent_id']);
            $table->dropUnique('vacation_2026_assignments_agent_id_unique');
            $table->index('agent_id');
            $table->unique(['agent_id', 'activity_id']);
            $table->foreign('agent_id')
                ->references('id')
                ->on('vacation_2026_agents')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('vacation_2026_assignments', function (Blueprint $table) {
            $table->dropForeign(['agent_id']);
            $table->dropUnique('vacation_2026_assignments_agent_id_activity_id_unique');
            $table->dropIndex(['agent_id']);
            $table->unique('agent_id');
            $table->foreign('agent_id')
                ->references('id')
                ->on('vacation_2026_agents')
                ->cascadeOnDelete();
        });
    }
};
