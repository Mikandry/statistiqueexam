<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('repartition_salles', function (Blueprint $table) {
            $table->string('axe_dispatching')->nullable()->after('saisi_par');
            $table->string('point_largage')->nullable()->after('axe_dispatching');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('repartition_salles', function (Blueprint $table) {
            $table->dropColumn(['axe_dispatching', 'point_largage']);
        });
    }
};
