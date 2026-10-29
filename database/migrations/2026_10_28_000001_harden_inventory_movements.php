<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Traçabilité des mouvements de matières :
 *  - request_token : jeton unique par formulaire, empêche l'enregistrement double d'un même mouvement ;
 *  - supporting_document : référence de la pièce justificative (bon de livraison, bon de sortie, PV…).
 * Aucune donnée existante n'est modifiée.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_stock_movements', function (Blueprint $table) {
            if (! Schema::hasColumn('inventory_stock_movements', 'request_token')) {
                $table->string('request_token', 64)->nullable()->unique();
            }
            if (! Schema::hasColumn('inventory_stock_movements', 'supporting_document')) {
                $table->string('supporting_document')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('inventory_stock_movements', function (Blueprint $table) {
            if (Schema::hasColumn('inventory_stock_movements', 'request_token')) {
                $table->dropUnique(['request_token']);
                $table->dropColumn('request_token');
            }
            if (Schema::hasColumn('inventory_stock_movements', 'supporting_document')) {
                $table->dropColumn('supporting_document');
            }
        });
    }
};
