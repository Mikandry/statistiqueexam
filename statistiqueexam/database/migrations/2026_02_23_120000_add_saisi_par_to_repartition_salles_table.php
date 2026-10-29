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
            $table->string('saisi_par')->nullable()->after('effectif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('repartition_salles', function (Blueprint $table) {
            $table->dropColumn('saisi_par');
        });
    }
};
