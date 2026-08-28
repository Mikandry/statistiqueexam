<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_document_settings', function (Blueprint $table) {
            $table->string('signataire_qualite')->nullable()->after('signataire');
        });
    }

    public function down(): void
    {
        Schema::table('hr_document_settings', fn (Blueprint $table) => $table->dropColumn('signataire_qualite'));
    }
};