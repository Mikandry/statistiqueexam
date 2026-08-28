<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_agents', function (Blueprint $table) {
            $table->string('budget')->nullable()->after('indice');
            $table->string('chapitre')->nullable()->after('budget');
        });
    }

    public function down(): void
    {
        Schema::table('hr_agents', function (Blueprint $table) {
            $table->dropColumn([
                'budget',
                'chapitre',
            ]);
        });
    }
};