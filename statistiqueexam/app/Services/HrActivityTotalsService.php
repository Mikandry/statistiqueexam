<?php

namespace App\Services;

use App\Models\HrAgent;
use App\Models\HrEvent;
use App\Models\Vacation2026Agent;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

/**
 * Bilan consolidé par agent : formation, absence, atelier, congé, sur une période.
 *
 * Règles :
 *  - seuls les événements « validé » ou « terminé » comptent ;
 *  - les jours sont des jours calendaires, bornés à la période choisie ;
 *  - chaque type est compté sur ses propres jours (colonne « jours bruts ») ;
 *  - le total consolidé compte chaque jour une seule fois, attribué au type le plus
 *    prioritaire (congé > absence > mission > formation > atelier > mise à disposition > autre) :
 *    deux événements qui se chevauchent ne sont donc jamais additionnés ;
 *  - congé, absence, formation et atelier restent des catégories distinctes.
 */
class HrActivityTotalsService
{
    public const COUNTED_STATUSES = ['valide', 'termine'];

    /** Ordre de priorité pour l'attribution d'un jour partagé. */
    public const PRIORITY = ['conge', 'autorisation_absence', 'mission', 'formation', 'atelier', 'mise_disposition', 'autre'];

    public function totals(Carbon $from, Carbon $to, ?string $search = null): Collection
    {
        $agents = HrAgent::query()
            ->with(['events' => fn ($q) => $q
                ->whereIn('status', self::COUNTED_STATUSES)
                ->whereDate('date_debut', '<=', $to)
                ->where(fn ($w) => $w->whereNull('date_fin')->orWhereDate('date_fin', '>=', $from))])
            ->when($search, fn ($q) => $q->where(fn ($w) => $w
                ->where('nom', 'like', "%{$search}%")->orWhere('prenoms', 'like', "%{$search}%")->orWhere('matricule', 'like', "%{$search}%")))
            ->orderBy('nom')->get();

        return $agents->map(fn (HrAgent $agent) => $this->forAgent($agent, $agent->events, $from, $to));
    }

    public function forAgent(HrAgent $agent, Collection $events, Carbon $from, Carbon $to): array
    {
        $daysByType = [];
        foreach ($events as $event) {
            foreach ($this->days($event, $from, $to) as $day) {
                $daysByType[$event->type][$day] = true;
            }
        }

        $raw = [];
        $owner = []; // jour => type prioritaire
        foreach (self::PRIORITY as $type) {
            foreach (array_keys($daysByType[$type] ?? []) as $day) {
                $raw[$type] = ($raw[$type] ?? 0) + 1;
                $owner[$day] ??= $type;
            }
        }
        // types hors liste de priorité : comptés en « autre »
        foreach ($daysByType as $type => $days) {
            if (! in_array($type, self::PRIORITY, true)) {
                foreach (array_keys($days) as $day) {
                    $raw['autre'] = ($raw['autre'] ?? 0) + 1;
                    $owner[$day] ??= 'autre';
                }
            }
        }

        $consolidated = array_count_values($owner);
        $unionDays = count($owner);
        $rawSum = array_sum($raw);

        return [
            'agent' => $agent,
            'raw' => $raw,
            'consolidated' => $consolidated,
            'union_days' => $unionDays,
            'overlap_days' => max(0, $rawSum - $unionDays),
            'events_count' => $events->count(),
        ];
    }

    /** @return array<int,string> jours (Y-m-d) de l'événement dans la période */
    private function days(HrEvent $event, Carbon $from, Carbon $to): array
    {
        if (! $event->date_debut) {
            return [];
        }
        $start = $event->date_debut->copy()->startOfDay()->max($from->copy()->startOfDay());
        $end = ($event->date_fin ?: $event->date_debut)->copy()->startOfDay()->min($to->copy()->startOfDay());
        if ($end->lt($start)) {
            return [];
        }

        return collect(CarbonPeriod::create($start, $end))->map(fn ($d) => $d->toDateString())->all();
    }

    /** Vue d'ensemble : effectifs, répartition, validations en attente, vacations. */
    public function overview(Carbon $from, Carbon $to): array
    {
        $agents = HrAgent::query()->get();
        $active = $agents->where('actif', true);

        $byUnit = $active->groupBy(fn ($a) => trim((string) ($a->direction ?: $a->service)) ?: 'Non renseigné')
            ->map->count()->sortDesc();
        $byFunction = $active->groupBy(fn ($a) => trim((string) $a->fonction) ?: 'Non renseignée')
            ->map->count()->sortDesc();

        $pending = HrEvent::query()->where('status', 'demande')->count();

        // Les agents de vacation sont rapprochés des agents RH par matricule (IM), sans création automatique.
        $vacationAgents = Vacation2026Agent::query()->withCount('assignments')->get();
        $matricules = $agents->pluck('matricule')->filter()->map(fn ($m) => mb_strtolower(trim((string) $m)))->all();
        $linked = $vacationAgents->filter(fn ($v) => in_array(mb_strtolower(trim((string) $v->im)), $matricules, true));

        return [
            'total' => $agents->count(),
            'active' => $active->count(),
            'inactive' => $agents->count() - $active->count(),
            'by_unit' => $byUnit,
            'by_function' => $byFunction,
            'pending_validations' => $pending,
            'vacation_agents' => $vacationAgents->count(),
            'vacation_assignments' => (int) $vacationAgents->sum('assignments_count'),
            'vacation_agents_linked_to_hr' => $linked->count(),
        ];
    }
}
