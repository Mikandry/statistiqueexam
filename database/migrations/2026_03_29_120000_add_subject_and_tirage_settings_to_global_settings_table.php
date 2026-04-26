<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->unsignedInteger('subject_soubique_ge_capacity')->default(6)->after('bepc_copy_margin_percent');
            $table->unsignedInteger('subject_soubique_subject_capacity')->default(9)->after('subject_soubique_ge_capacity');
            $table->unsignedInteger('sord_sheet_page_capacity')->default(16)->after('subject_soubique_subject_capacity');

            $table->unsignedInteger('cepe_pages_francais')->default(1)->after('sord_sheet_page_capacity');
            $table->unsignedInteger('cepe_pages_connaissances_usuelles')->default(1)->after('cepe_pages_francais');
            $table->unsignedInteger('cepe_pages_geographie')->default(1)->after('cepe_pages_connaissances_usuelles');
            $table->unsignedInteger('cepe_pages_malagasy')->default(1)->after('cepe_pages_geographie');
            $table->unsignedInteger('cepe_pages_operation')->default(1)->after('cepe_pages_malagasy');
            $table->unsignedInteger('cepe_pages_probleme')->default(1)->after('cepe_pages_operation');
            $table->unsignedInteger('cepe_pages_tffmom')->default(1)->after('cepe_pages_probleme');

            $table->unsignedInteger('bepc_pages_malagasy')->default(1)->after('cepe_pages_tffmom');
            $table->unsignedInteger('bepc_pages_svt')->default(1)->after('bepc_pages_malagasy');
            $table->unsignedInteger('bepc_pages_francais')->default(1)->after('bepc_pages_svt');
            $table->unsignedInteger('bepc_pages_anglais')->default(1)->after('bepc_pages_francais');
            $table->unsignedInteger('bepc_pages_esp')->default(1)->after('bepc_pages_anglais');
            $table->unsignedInteger('bepc_pages_pc')->default(1)->after('bepc_pages_esp');
            $table->unsignedInteger('bepc_pages_math')->default(1)->after('bepc_pages_pc');
            $table->unsignedInteger('bepc_pages_hg')->default(1)->after('bepc_pages_math');
            $table->unsignedInteger('bepc_pages_all')->default(1)->after('bepc_pages_hg');
        });

        DB::table('global_settings')->update([
            'subject_soubique_ge_capacity' => 6,
            'subject_soubique_subject_capacity' => 9,
            'sord_sheet_page_capacity' => 16,
            'cepe_pages_francais' => 1,
            'cepe_pages_connaissances_usuelles' => 1,
            'cepe_pages_geographie' => 1,
            'cepe_pages_malagasy' => 1,
            'cepe_pages_operation' => 1,
            'cepe_pages_probleme' => 1,
            'cepe_pages_tffmom' => 1,
            'bepc_pages_malagasy' => 1,
            'bepc_pages_svt' => 1,
            'bepc_pages_francais' => 1,
            'bepc_pages_anglais' => 1,
            'bepc_pages_esp' => 1,
            'bepc_pages_pc' => 1,
            'bepc_pages_math' => 1,
            'bepc_pages_hg' => 1,
            'bepc_pages_all' => 1,
        ]);
    }

    public function down(): void
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->dropColumn([
                'subject_soubique_ge_capacity',
                'subject_soubique_subject_capacity',
                'sord_sheet_page_capacity',
                'cepe_pages_francais',
                'cepe_pages_connaissances_usuelles',
                'cepe_pages_geographie',
                'cepe_pages_malagasy',
                'cepe_pages_operation',
                'cepe_pages_probleme',
                'cepe_pages_tffmom',
                'bepc_pages_malagasy',
                'bepc_pages_svt',
                'bepc_pages_francais',
                'bepc_pages_anglais',
                'bepc_pages_esp',
                'bepc_pages_pc',
                'bepc_pages_math',
                'bepc_pages_hg',
                'bepc_pages_all',
            ]);
        });
    }
};
