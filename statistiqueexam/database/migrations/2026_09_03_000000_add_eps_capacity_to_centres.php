<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('centre_corrections') && ! Schema::hasColumn('centre_corrections', 'eps_capacity')) {
            Schema::table('centre_corrections', function (Blueprint $table) {
                $table->unsignedTinyInteger('eps_capacity')->default(1)->after('is_eps_gym');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('centre_corrections') && Schema::hasColumn('centre_corrections', 'eps_capacity')) {
            Schema::table('centre_corrections', function (Blueprint $table) {
                $table->dropColumn('eps_capacity');
            });
        }
    }
};
