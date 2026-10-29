<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Hiérarchie administrative et centres.
 *
 *  1. provinces                : les 6 provinces (référence). Les enregistrements historiques,
 *                                antérieurs aux DREN, se rattachent à une province.
 *  2. drens.province_id        : province de la DREN (reprend l'ancien texte drens.province s'il existe).
 *  3. ciscos.province_id       : province de la CISCO ; ciscos.dren_id devient NULLABLE pour
 *                                conserver les CISCO historiques sans les forcer dans une DREN.
 *  4. centre_corrections.eps_capacity : élargi (tinyint 0-255 -> entier) : c'est le nombre de
 *                                candidats EPS d'un centre EPS seul, un tinyint ne pouvait pas le contenir.
 *  5. centre_ecrits.same_site_as_correction : déclare explicitement qu'un centre d'écrit est
 *                                le même lieu que son centre de correction (jumelés) sans dépendre du nom.
 *  6. transcription_centres    : centres de transcription, distincts des centres d'écrit et de
 *                                correction ; une CISCO peut en avoir plusieurs.
 */
return new class extends Migration
{
    public const PROVINCES = ['Antananarivo', 'Antsiranana', 'Fianarantsoa', 'Mahajanga', 'Toamasina', 'Toliara'];

    public function up(): void
    {
        if (! Schema::hasTable('provinces')) {
            Schema::create('provinces', function (Blueprint $table) {
                $table->id();
                $table->string('nom')->unique();
                $table->timestamps();
            });
        }
        foreach (self::PROVINCES as $nom) {
            if (! DB::table('provinces')->where('nom', $nom)->exists()) {
                DB::table('provinces')->insert(['nom' => $nom, 'created_at' => now(), 'updated_at' => now()]);
            }
        }

        if (Schema::hasTable('drens') && ! Schema::hasColumn('drens', 'province_id')) {
            Schema::table('drens', function (Blueprint $table) {
                $table->foreignId('province_id')->nullable()->constrained('provinces')->nullOnDelete();
            });
        }
        if (Schema::hasTable('drens') && Schema::hasColumn('drens', 'province')) {
            $ids = DB::table('provinces')->pluck('id', 'nom');
            foreach (DB::table('drens')->whereNull('province_id')->whereNotNull('province')->get(['id', 'province']) as $dren) {
                if (isset($ids[$dren->province])) {
                    DB::table('drens')->where('id', $dren->id)->update(['province_id' => $ids[$dren->province]]);
                }
            }
        }

        if (Schema::hasTable('ciscos')) {
            if (! Schema::hasColumn('ciscos', 'province_id')) {
                Schema::table('ciscos', function (Blueprint $table) {
                    $table->foreignId('province_id')->nullable()->constrained('provinces')->nullOnDelete();
                });
            }
            Schema::table('ciscos', function (Blueprint $table) {
                $table->unsignedBigInteger('dren_id')->nullable()->change();
            });
        }

        if (Schema::hasTable('centre_corrections') && Schema::hasColumn('centre_corrections', 'eps_capacity')) {
            Schema::table('centre_corrections', function (Blueprint $table) {
                $table->unsignedInteger('eps_capacity')->nullable()->default(null)->change();
            });
        }

        if (Schema::hasTable('centre_ecrits') && ! Schema::hasColumn('centre_ecrits', 'same_site_as_correction')) {
            Schema::table('centre_ecrits', function (Blueprint $table) {
                $table->boolean('same_site_as_correction')->default(false);
            });
        }

        if (! Schema::hasTable('transcription_centres')) {
            Schema::create('transcription_centres', function (Blueprint $table) {
                $table->id();
                $table->foreignId('cisco_id')->constrained('ciscos')->cascadeOnDelete();
                $table->string('nom');
                $table->string('localisation')->nullable();
                $table->unsignedSmallInteger('ordre')->default(0);
                // Candidats transcrits par examen. NULL = partage égal des candidats de la CISCO
                // entre ses centres de transcription.
                $table->unsignedInteger('candidats_cepe')->nullable();
                $table->unsignedInteger('candidats_bepc')->nullable();
                $table->timestamps();

                $table->unique(['cisco_id', 'nom']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('transcription_centres');

        if (Schema::hasColumn('centre_ecrits', 'same_site_as_correction')) {
            Schema::table('centre_ecrits', fn (Blueprint $t) => $t->dropColumn('same_site_as_correction'));
        }
        if (Schema::hasColumn('ciscos', 'province_id')) {
            Schema::table('ciscos', function (Blueprint $t) {
                $t->dropConstrainedForeignId('province_id');
            });
        }
        if (Schema::hasColumn('drens', 'province_id')) {
            Schema::table('drens', function (Blueprint $t) {
                $t->dropConstrainedForeignId('province_id');
            });
        }
        Schema::dropIfExists('provinces');
    }
};
