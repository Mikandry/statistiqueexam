<?php

namespace App\Services;

use App\Models\Vacation2026Activity;
use App\Models\Vacation2026Plan;
use App\Models\Vacation2026Session;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class VacationPlanningService
{
    /** Initialise les activités CENTRAL de l'examen puis calcule leurs dates. */
    public function generate(Vacation2026Session $session, bool $preserveManual = true): Collection
    {
        $activities = Vacation2026Activity::query()->where('examen', $session->examen)->where('level', 'CENTRAL')->orderBy('ordre')->get();
        foreach ($activities as $activity) {
            Vacation2026Plan::query()->firstOrCreate(
                ['session_id' => $session->id, 'activity_id' => $activity->id],
                ['ordre' => $activity->ordre, 'duree_jours' => max(1, (int) $activity->nb_jours), 'statut' => 'PREVISIONNEL']
            );
        }

        return $this->recalculate($session, $preserveManual);
    }

    public function recalculate(Vacation2026Session $session, bool $preserveManual = true): Collection
    {
        $plans = $session->plans()->with('activity')->orderBy('ordre')->get();
        $pivot = Carbon::parse($session->date_session);
        $sessionEnd = Carbon::parse($session->date_fin_session ?? $session->date_session);

        foreach (['AVANT_SESSION', 'PENDANT_SESSION', 'APRES_SESSION'] as $phase) {
            $group = $plans->filter(fn ($plan) => ($plan->activity?->phase ?? 'AVANT_SESSION') === $phase)->values();
            if ($phase === 'AVANT_SESSION') {
                $cursor = $pivot->copy()->subDay();
                foreach ($group->reverse() as $plan) {
                    if ($preserveManual && $plan->manuel) { $cursor = $plan->date_debut->copy()->subDay(); continue; }
                    $end = $cursor->copy();
                    $start = $end->copy()->subDays(max(1, $plan->duree_jours) - 1);
                    if ($plan->chevauchement_autorise && $group->count() > 1) { $start = $end->copy()->subDays(max(1, $plan->duree_jours) - 1); }
                    $this->saveAutomatic($plan, $start, $end);
                    if (! $plan->chevauchement_autorise) { $cursor = $start->copy()->subDay(); }
                }
            } else {
                $cursor = $phase === 'PENDANT_SESSION' ? $pivot->copy() : $sessionEnd->copy()->addDay();
                foreach ($group as $plan) {
                    if ($preserveManual && $plan->manuel) { $cursor = $plan->date_fin->copy()->addDay(); continue; }
                    $start = $plan->chevauchement_autorise ? ($phase === 'PENDANT_SESSION' ? $pivot->copy() : $sessionEnd->copy()->addDay()) : $cursor->copy();
                    $end = $start->copy()->addDays(max(1, $plan->duree_jours) - 1);
                    $this->saveAutomatic($plan, $start, $end);
                    if (! $plan->chevauchement_autorise) { $cursor = $end->copy()->addDay(); }
                }
            }
        }

        // Les dépendances sont appliquées après le premier placement. Elles
        // prévalent sur l'ordre affiché lorsqu'une activité doit finir avant
        // le démarrage d'une autre activité.
        $plans = $session->plans()->with('activity')->orderBy('ordre')->get();
        foreach ($plans as $plan) {
            if (($preserveManual && $plan->manuel) || empty($plan->dependency_ids)) {
                continue;
            }
            $latestEnd = $plans->whereIn('id', $plan->dependency_ids)->max('date_fin');
            if ($latestEnd && $plan->date_debut && $plan->date_debut->lessThanOrEqualTo($latestEnd)) {
                $start = $latestEnd->copy()->addDay();
                $this->saveAutomatic($plan, $start, $start->copy()->addDays(max(1, $plan->duree_jours) - 1));
            }
        }

        return $session->plans()->with('activity')->orderBy('ordre')->get();
    }

    private function saveAutomatic(Vacation2026Plan $plan, Carbon $start, Carbon $end): void
    {
        $plan->update(['date_debut' => $start, 'date_fin' => $end, 'manuel' => false]);
    }
}
