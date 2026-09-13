<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('vacation_2026_activities')
            ->where('level', 'CENTRE')
            ->where('rule_key', 'centre_before_session')
            ->update(['libelle' => "Réception des dossiers d'inscription", 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('vacation_2026_activities')
            ->where('level', 'CENTRE')
            ->where('rule_key', 'centre_before_session')
            ->update(['libelle' => 'Préparation avant session', 'updated_at' => now()]);
    }
};
