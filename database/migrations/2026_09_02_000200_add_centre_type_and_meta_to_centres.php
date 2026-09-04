<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the "type de centre" and EPS/GYM flags required by the 2026 vacation
 * module (Décret N°2026-1257).
 *
 * The centre type drives which activities are shown for a centre:
 *   - CENTRE D'ECRIT SEULEMENT      -> written-session activities
 *   - CENTRE DE CORRECTION SEULEMENT -> correction activities
 *   - CENTRE D'ECRIT ET CORRECTION   -> both
 *   - CENTRE DE TRANSCRIPTION        -> transcription activities
 *   - SOUS-CENTRE
 *   - EPS/GYM                        -> EPS activities (written exam = separate)
 *
 * Values are stored on the existing centre_ecrits / centre_corrections tables
 * (no new table, keeps existing relationships intact). Backward compatible:
 * existing centres keep their sensible default.
 */
return new class extends Migration
{
    public const CENTRE_TYPES = [
        "CENTRE D'ECRIT SEULEMENT",
        "CENTRE DE CORRECTION SEULEMENT",
        "CENTRE D'ECRIT ET CORRECTION JUMELES",
        'CENTRE DE TRANSCRIPTION',
        'SOUS-CENTRE',
        'EPS/GYM',
    ];

    public function up(): void
    {
        if (Schema::hasTable('centre_ecrits')) {
            Schema::table('centre_ecrits', function (Blueprint $table) {
                if (! Schema::hasColumn('centre_ecrits', 'centre_type')) {
                    $table->string('centre_type', 60)->default("CENTRE D'ECRIT SEULEMENT")->after('type_examen');
                }
                if (! Schema::hasColumn('centre_ecrits', 'is_eps_gym')) {
                    $table->boolean('is_eps_gym')->default(false)->after('centre_type');
                }
            });
        }

        if (Schema::hasTable('centre_corrections')) {
            Schema::table('centre_corrections', function (Blueprint $table) {
                if (! Schema::hasColumn('centre_corrections', 'centre_type')) {
                    $table->string('centre_type', 60)->default('CENTRE DE CORRECTION SEULEMENT')->after('type_examen');
                }
                if (! Schema::hasColumn('centre_corrections', 'is_eps_gym')) {
                    $table->boolean('is_eps_gym')->default(false)->after('centre_type');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('centre_ecrits')) {
            Schema::table('centre_ecrits', function (Blueprint $table) {
                $table->dropColumn(['centre_type', 'is_eps_gym']);
            });
        }
        if (Schema::hasTable('centre_corrections')) {
            Schema::table('centre_corrections', function (Blueprint $table) {
                $table->dropColumn(['centre_type', 'is_eps_gym']);
            });
        }
    }
};