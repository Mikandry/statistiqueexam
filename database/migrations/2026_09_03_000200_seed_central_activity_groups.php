<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $activities = DB::table('vacation_2026_activities')
            ->where('level', 'CENTRAL')
            ->where(function ($query) {
                $query->where('libelle', 'like', '%sujet%')
                    ->orWhere('libelle', 'like', '%sous-pli%')
                    ->orWhere('libelle', 'like', '%enveloppe%')
                    ->orWhere('libelle', 'like', '%livres%');
            })
            ->get();

        foreach ($activities as $activity) {
            $base = (int) ($activity->max_agents ?? 0);
            $groups = ['OG', 'SUP', 'COORDO', 'SOUS PLI'];
            foreach ($groups as $index => $groupe) {
                $personnel = intdiv($base, 4) + ($index < ($base % 4) ? 1 : 0);
                DB::table('vacation_2026_activity_groups')->updateOrInsert(
                    ['activity_id' => $activity->id, 'groupe' => $groupe],
                    [
                        'personnel' => $personnel,
                        'nb_jours' => (int) $activity->nb_jours,
                        'taux' => (float) ($activity->taux_activite ?? 0),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        DB::table('vacation_2026_activity_groups')->delete();
    }
};
