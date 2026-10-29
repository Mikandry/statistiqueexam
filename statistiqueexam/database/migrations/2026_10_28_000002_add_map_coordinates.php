<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Carte : coordonnées géographiques des centres et des points de largage.
 * Colonnes nullables : un centre sans coordonnées reste valide, il n'apparaît simplement pas sur la carte.
 */
return new class extends Migration
{
    private const TABLES = ['centre_ecrits', 'centre_corrections', 'transcription_centres'];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'latitude')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->decimal('latitude', 9, 6)->nullable();
                    $t->decimal('longitude', 9, 6)->nullable();
                });
            }
        }

        if (! Schema::hasTable('dispatching_point_coordinates')) {
            Schema::create('dispatching_point_coordinates', function (Blueprint $t) {
                $t->id();
                $t->string('axe', 191);
                $t->string('point', 191);
                $t->decimal('latitude', 9, 6)->nullable();
                $t->decimal('longitude', 9, 6)->nullable();
                $t->timestamps();
                $t->unique(['axe', 'point']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('dispatching_point_coordinates');
        foreach (self::TABLES as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'latitude')) {
                Schema::table($table, fn (Blueprint $t) => $t->dropColumn(['latitude', 'longitude']));
            }
        }
    }
};
