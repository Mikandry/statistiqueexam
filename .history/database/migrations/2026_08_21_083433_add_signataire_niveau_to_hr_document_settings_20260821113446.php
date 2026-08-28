<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_document_settings', function (Blueprint $table) {
            $table->string('signataire_niveau')
                ->default('directeur')
                ->after('signataire_qualite');

            $table->string('ville')
                ->default('Antananarivo')
                ->after('signataire_niveau');
        });
    }

    public function down(): void
    {
        Schema::table('hr_document_settings', function (Blueprint $table) {
            $table->dropColumn([
                'signataire_niveau',
                'ville',
            ]);
        });
    }
};