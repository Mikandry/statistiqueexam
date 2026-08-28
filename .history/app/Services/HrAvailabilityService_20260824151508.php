<?php

namespace App\Services;

use App\Models\HrAgent;
use App\Models\HrEvent;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class HrAvailabilityService
{
    public function dashboard(?Carbon $date = null, ?HrAgent $onlyAgent = null): array
    {
        $date ??= today();

        $query = HrAgent::query()
            ->where('actif', true)
            ->with([
                'currentAssignment',
                'assignments',
                'events',
            ])
            ->orderBy('nom');

        if ($onlyAgent) {
            $query->whereKey($onlyAgent->id);
        }

        $agents = $query->get();

        $situations = $agents
            ->map(fn (HrAgent $agent) => $this->situation($agent, $date))
            ->values();

        return [
            'agents' => $agents,
            'situations' => $situations,

            'stats' => [
                'total' => $situations->count(),

                'present' => $situations
                    ->where('code', 'present')
                    ->count(),

                'conge' => $situations
                    ->where('code', 'conge')
                    ->count(),

                'mission' => $situations
                    ->where('code', 'mission')
                    ->count(),

                'formation' => $situations
                    ->filter(fn (array $s) => in_array(
                        $s['code'],
                        ['formation', 'formation_partielle'],
                        true
                    ))
                    ->count(),

                'autorisation_absence' => $situations
                    ->filter(fn (array $s) => in_array(
                        $s['code'],
                        ['autorisation_absence', 'autorisation_absence_partielle'],
                        true
                    ))
                    ->count(),

                'mise_disposition' => $situations
                    ->where('code', 'mise_disposition')
                    ->count(),

                'autre_indisponibilite' => $situations
                    ->where('code', 'autre')
                    ->count(),

                'affectation_temporaire' => $situations
                    ->where('code', 'affectation_temporaire')
                    ->count(),

                'sans_affectation' => $situations
                    ->where('code', 'sans_affectation')
                    ->count(),

                'formation_partielle' => $situations
                    ->where('code', 'formation_partielle')
                    ->count(),
            ],
        ];
    }

    public function situation(HrAgent $agent, Carbon $date): array
    {
        $date = $date->copy()->startOfDay();

        $events = $agent->events
            ->filter(fn (HrEvent $event) =>
                $event->status === 'valide'
                && $event->date_debut->lte($date)
                && (
                    !$event->date_fin
                    || $event->date_fin->gte($date)
                )
            );

        /*
         * Full-day events have priority.
         */
        $fullDay = $events->first(
            fn (HrEvent $event) => $event->isFullDay()
        );

        if ($fullDay) {
            return $this->eventSituation($agent, $fullDay);
        }

        /*
         * Partial-day events such as 14:00–16:00 training.
         */
        $partialEvent = $events->first(
            fn (HrEvent $event) =>
                !$event->isFullDay()
                && $this->isApplicableDay($event, $date)
        );

        if ($partialEvent) {
            return [
                'agent' => $agent,
                'code' => $partialEvent->type . '_partielle',
                'label' => HrEvent::TYPES[$partialEvent->type]
                    ?? $partialEvent->type,

                'start' => $partialEvent->date_debut,
                'end' => $partialEvent->date_fin,

                'time_start' => $partialEvent->heure_debut,
                'time_end' => $partialEvent->heure_fin,

                'availability' => sprintf(
                    '%s à %s',
                    substr((string) $partialEvent->heure_debut, 0, 5),
                    substr((string) $partialEvent->heure_fin, 0, 5)
                ),

                'event' => $partialEvent,
            ];
        }

        /*
         * Find assignment active on this date.
         */
        $assignment = $agent->assignments->first(
            fn ($item) =>
                $item->date_debut->lte($date)
                && (
                    !$item->date_fin
                    || $item->date_fin->gte($date)
                )
        );

        if (!$assignment) {
            return [
                'agent' => $agent,
                'code' => 'sans_affectation',
                'label' => 'Sans affectation',
                'start' => null,
                'end' => null,
                'availability' => 'À régulariser',
                'assignment' => null,
                'event' => null,
            ];
        }

        $temporary = $assignment->date_fin !== null;

        return [
            'agent' => $agent,

            'code' => $temporary
                ? 'affectation_temporaire'
                : 'present',

            'label' => $temporary
                ? 'Affectation temporaire'
                : 'Présent',

            'start' => $assignment->date_debut,
            'end' => $assignment->date_fin,

            'availability' =>
                $assignment->service
                ?: $assignment->direction
                ?: 'Direction',

            'assignment' => $assignment,
            'event' => null,
        ];
    }

    private function eventSituation(
        HrAgent $agent,
        HrEvent $event
    ): array {
        return [
            'agent' => $agent,

            'code' => $event->type,

            'label' => HrEvent::TYPES[$event->type]
                ?? $event->type,

            'start' => $event->date_debut,

            'end' => $event->date_fin,

            'time_start' => $event->heure_debut,

            'time_end' => $event->heure_fin,

            'availability' =>
                $event->title
                ?: $event->motif
                ?: 'Indisponible',

            'event' => $event,
        ];
    }

    private function isApplicableDay(
        HrEvent $event,
        Carbon $date
    ): bool {
        if (!$event->jours_semaine) {
            return true;
        }

        /*
         * Carbon:
         * 1 = Monday
         * 7 = Sunday
         */
        return in_array(
            $date->dayOfWeekIso,
            $event->jours_semaine,
            true
        );
    }

    public function eventDays(HrEvent $event): int
    {
        return (
            $event->date_fin ?: $event->date_debut
        )->diffInDays($event->date_debut) + 1;
    }

    public function approvedEvents(
        HrAgent $agent,
        ?Carbon $from = null,
        ?Carbon $to = null
    ): Collection {
        return $agent->events
            ->filter(function (HrEvent $event) use ($from, $to) {

                if ($event->status !== 'valide') {
                    return false;
                }

                if ($from && $event->date_fin?->lt($from)) {
                    return false;
                }

                if ($to && $event->date_debut->gt($to)) {
                    return false;
                }

                return true;
            });
    }
}