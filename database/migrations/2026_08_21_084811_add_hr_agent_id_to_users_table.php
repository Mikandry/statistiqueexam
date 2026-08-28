<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('hr_agent_id')
                ->nullable()
                ->after('role')
                ->constrained('hr_agents')
                ->nullOnDelete();

            $table->index('hr_agent_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['hr_agent_id']);
            $table->dropIndex(['hr_agent_id']);
            $table->dropColumn('hr_agent_id');
        });
    }
};