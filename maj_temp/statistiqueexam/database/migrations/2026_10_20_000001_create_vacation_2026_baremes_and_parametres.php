<?php

use App\Support\MadagascarProvinces;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Vacation 2026 — module « Besoins & Montants ».
 *
 *  - vacation_2026_baremes    : taux d'indemnité (fixés par arrêté, Art. 5 et 7 du décret),
 *                               par code de rôle et par examen. Aucun taux n'est inventé :
 *                               la table est vide tant que l'arrêté n'est pas saisi.
 *  - vacation_2026_parametres : paramètres de calcul (année, nombre de provinces, ...).
 *  - drens.province / drens.is_chef_lieu_province : nécessaires pour les travaux
 *    « au niveau provincial » (finalisation, validation, testing — CEPE).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('vacation_2026_baremes')) {
            Schema::create('vacation_2026_baremes', function (Blueprint $table) {
                $table->id();
                $table->string('code', 80);
                $table->string('examen', 8);            // CEPE | BEPC
                $table->string('mode', 10)->default('JOUR'); // JOUR (taux/jour) | FORFAIT (taux/activité)
                $table->decimal('montant', 14, 2)->default(0);
                $table->timestamps();

                $table->unique(['code', 'examen']);
            });
        }

        if (! Schema::hasTable('vacation_2026_parametres')) {
            Schema::create('vacation_2026_parametres', function (Blueprint $table) {
                $table->id();
                $table->string('cle', 80)->unique();
                $table->text('valeur')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('drens')) {
            Schema::table('drens', function (Blueprint $table) {
                if (! Schema::hasColumn('drens', 'province')) {
                    $table->string('province', 60)->nullable();
                }
                if (! Schema::hasColumn('drens', 'is_chef_lieu_province')) {
                    $table->boolean('is_chef_lieu_province')->default(false);
                }
            });

            // Pré-remplissage automatique (modifiable ensuite dans « Paramètres »).
            foreach (DB::table('drens')->get(['id', 'nom']) as $dren) {
                $guess = MadagascarProvinces::guess((string) $dren->nom);
                if ($guess !== null) {
                    DB::table('drens')->where('id', $dren->id)->update([
                        'province' => $guess['province'],
                        'is_chef_lieu_province' => $guess['chef_lieu'],
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('vacation_2026_baremes');
        Schema::dropIfExists('vacation_2026_parametres');

        if (Schema::hasTable('drens')) {
            Schema::table('drens', function (Blueprint $table) {
                foreach (['province', 'is_chef_lieu_province'] as $column) {
                    if (Schema::hasColumn('drens', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
