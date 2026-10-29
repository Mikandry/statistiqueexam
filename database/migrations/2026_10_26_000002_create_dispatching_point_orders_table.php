<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ordre de livraison des points de largage, choisi par l'utilisateur et conservé en base.
 * Une ligne = la position d'un point de largage au sein d'un axe de dispatching.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('dispatching_point_orders')) {
            return;
        }
        Schema::create('dispatching_point_orders', function (Blueprint $table) {
            $table->id();
            $table->string('axe', 191);
            $table->string('point', 191);
            $table->unsignedSmallInteger('position')->default(1);
            $table->timestamps();
            $table->unique(['axe', 'point']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispatching_point_orders');
    }
};
