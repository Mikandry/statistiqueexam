<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vacation_2026_settings', function (Blueprint $table) {
            $table->string('note_titre')->nullable()->after('considerant');
            $table->string('decision_titre')->nullable()->after('note_titre');
            $table->string('presence_titre')->nullable()->after('decision_titre');
            $table->string('decompte_titre')->nullable()->after('presence_titre');
            $table->string('decision_reference')->nullable()->after('decompte_titre');
        });
    }

    public function down(): void
    {
        Schema::table('vacation_2026_settings', function (Blueprint $table) {
            $table->dropColumn(['note_titre', 'decision_titre', 'presence_titre', 'decompte_titre', 'decision_reference']);
        });
    }
};
