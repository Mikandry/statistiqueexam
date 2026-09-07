<?php

namespace App\Services;

use App\Models\HrAgent;
use App\Models\HrEvent;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class HrAvailabilityService
{
    /**
     * Dashboard RH.
     *
     * Règle principale :
     *
     * - Un agent actif avec un événement d'indisponibilité actif
     *   prend le statut correspondant à cet événement.
     *
     * - Un agent actif sans événement d'indisponibilité actif
     *   est considéré comme PRÉSENT.
     *
     * - L'absence d'affectation ne signifie donc pas que l'agent
     *   est absent.
     */
    public function dashboard(
        ?Carbon $date = null,
        ?HrAgent $onlyAgent = null
    ): array {
        $date ??= today();

        $date = $date->copy()->startOfDay();

        /*
        |--------------------------------------------------------------------------
        | Agents actifs
        |--------------------------------------------------------------------------
        */
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

        /*
        |--------------------------------------------------------------------------
        | Situation de chaque agent
        |--------------------------------------------------------------------------
        */
        $situations = $agents
            ->map(
                fn (HrAgent $agent) =>
                    $this->situation($agent, $date)
            )
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Événements suivis
        |--------------------------------------------------------------------------
        |
        | On prend les événements demandés ou validés.
        |
        */
        $trackedEvents = $agents->flatMap(
            fn (HrAgent $agent) =>
                $agent->events->filter(
                    fn (HrEvent $event) =>
                        in_array(
                            $event->status,
                            ['valide', 'demande'],
                            true
                        )
                )
        );

        /*
        |--------------------------------------------------------------------------
        | Événements actifs à la date sélectionnée
        |--------------------------------------------------------------------------
        */
        $activeEvents = $trackedEvents
            ->filter(
                fn (HrEvent $event) =>
                    $this->eventIsActiveOnDate(
                        $event,
                        $date
                    )
            )
            ->values();

        /*
        |--------------------------------------------------------------------------
        | STATISTIQUES
        |--------------------------------------------------------------------------
        |
        | Les statistiques principales sont calculées à partir
        | des situations réellement affichées.
        |
        */
        $stats = [

            /*
            |--------------------------------------------------------------------------
            | Total agents actifs
            |--------------------------------------------------------------------------
            */
            'total' => $agents->count(),

            /*
            |--------------------------------------------------------------------------
            | Présents
            |--------------------------------------------------------------------------
            |
            | Un agent est présent lorsqu'il n'a aucun événement
            | d'indisponibilité actif.
            |
            */
            'present' => $situations
                ->where('code', 'present')
                ->count(),

            /*
            |--------------------------------------------------------------------------
            | Congés
            |--------------------------------------------------------------------------
            */
            'conge' => $situations
                ->filter(
                    fn (array $s) =>
                        in_array(
                            $s['code'],
                            [
                                'conge',
                                'conge_partielle',
                            ],
                            true
                        )
                )
                ->count(),

            /*
            |--------------------------------------------------------------------------
            | Missions
            |--------------------------------------------------------------------------
            */
            'mission' => $situations
                ->filter(
                    fn (array $s) =>
                        in_array(
                            $s['code'],
                            [
                                'mission',
                                'mission_partielle',
                            ],
                            true
                        )
                )
                ->count(),

            /*
            |--------------------------------------------------------------------------
            | Formations
            |--------------------------------------------------------------------------
            */
            'formation' => $situations
                ->filter(
                    fn (array $s) =>
                        in_array(
                            $s['code'],
                            [
                                'formation',
                                'formation_partielle',
                            ],
                            true
                        )
                )
                ->count(),

            /*
            |--------------------------------------------------------------------------
            | Autorisations d'absence
            |--------------------------------------------------------------------------
            */
            'autorisation_absence' => $situations
                ->filter(
                    fn (array $s) =>
                        in_array(
                            $s['code'],
                            [
                                'autorisation_absence',
                                'autorisation_absence_partielle',
                            ],
                            true
                        )
                )
                ->count(),

            /*
            |--------------------------------------------------------------------------
            | Mise à disposition
            |--------------------------------------------------------------------------
            */
            'mise_disposition' => $situations
                ->filter(
                    fn (array $s) =>
                        in_array(
                            $s['code'],
                            [
                                'mise_disposition',
                                'mise_disposition_partielle',
                            ],
                            true
                        )
                )
                ->count(),

            /*
            |--------------------------------------------------------------------------
            | Autre indisponibilité
            |--------------------------------------------------------------------------
            */
            'autre_indisponibilite' => $situations
                ->filter(
                    fn (array $s) =>
                        in_array(
                            $s['code'],
                            [
                                'autre',
                                'autre_partielle',
                            ],
                            true
                        )
                )
                ->count(),

            /*
            |--------------------------------------------------------------------------
            | Affectations temporaires
            |--------------------------------------------------------------------------
            |
            | Cette statistique est informative.
            | Un agent avec une affectation temporaire reste PRÉSENT
            | s'il n'a aucun événement d'indisponibilité.
            |
            */
            'affectation_temporaire' => $situations
                ->filter(
                    fn (array $s) =>
                        ($s['assignment'] ?? null)
                        && $s['assignment']->date_fin !== null
                        && $s['code'] === 'present'
                )
                ->count(),

            /*
            |--------------------------------------------------------------------------
            | Sans affectation
            |--------------------------------------------------------------------------
            |
            | Cette statistique est indépendante de la présence.
            |
            */
            'sans_affectation' => $situations
                ->filter(
                    fn (array $s) =>
                        $s['code'] === 'present'
                        && empty($s['assignment'])
                )
                ->count(),

            /*
            |--------------------------------------------------------------------------
            | Formation partielle
            |--------------------------------------------------------------------------
            */
            'formation_partielle' => $situations
                ->where('code', 'formation_partielle')
                ->count(),
        ];

        /*
        |--------------------------------------------------------------------------
        | Récapitulatif congés + autorisations
        |--------------------------------------------------------------------------
        */
        $eventSummary = $agents
            ->map(
                function (HrAgent $agent) use ($trackedEvents) {

                    $events = $trackedEvents->where(
                        'agent_id',
                        $agent->id
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Congés
                    |--------------------------------------------------------------------------
                    */
                    $leaves = $events->filter(
                        fn (HrEvent $event) =>
                            $event->type === 'conge'
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Autorisations
                    |--------------------------------------------------------------------------
                    */
                    $absences = $events->filter(
                        fn (HrEvent $event) =>
                            $event->type === 'autorisation_absence'
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Nombre de jours de congé
                    |--------------------------------------------------------------------------
                    */
                    $leaveDays = $leaves->sum(
                        fn (HrEvent $event) =>
                            $this->eventDays($event)
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Nombre de jours d'autorisation
                    |--------------------------------------------------------------------------
                    */
                    $absenceDays = $absences->sum(
                        fn (HrEvent $event) =>
                            $this->eventDays($event)
                    );

                    return [
                        'agent' => $agent,

                        'leave_count' =>
                            $leaves->count(),

                        'leave_days' =>
                            $leaveDays,

                        'absence_count' =>
                            $absences->count(),

                        'absence_days' =>
                            $absenceDays,

                        'total_days' =>
                            $leaveDays + $absenceDays,
                    ];
                }
            )
            ->sortByDesc('total_days')
            ->values()
            ->map(
                function (
                    array $summary,
                    int $index
                ) {

                    $summary['rank'] =
                        $index + 1;

                    return $summary;
                }
            )
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Retour
        |--------------------------------------------------------------------------
        */
        return [
            'agents' =>
                $agents,

            'situations' =>
                $situations,

            'stats' =>
                $stats,

            'eventSummary' =>
                $eventSummary,
        ];
    }


    /**
     * Détermine la situation d'un agent à une date donnée.
     *
     * IMPORTANT :
     *
     * L'absence d'affectation ne signifie pas absence.
     *
     * Si l'agent est actif et n'a aucun événement actif,
     * il est considéré comme présent.
     */
    public function situation(
        HrAgent $agent,
        Carbon $date
    ): array {

        $date = $date
            ->copy()
            ->startOfDay();

        /*
        |--------------------------------------------------------------------------
        | Événements actifs
        |--------------------------------------------------------------------------
        */
        $events = $agent->events
            ->filter(
                fn (HrEvent $event) =>
                    in_array(
                        $event->status,
                        ['valide', 'demande'],
                        true
                    )
                    && $this->eventIsActiveOnDate(
                        $event,
                        $date
                    )
            )
            ->sortBy(
                function (HrEvent $event) {

                    /*
                    |--------------------------------------------------------------------------
                    | Priorité :
                    | 1. événement pleine journée
                    | 2. événement partiel
                    |--------------------------------------------------------------------------
                    */
                    return $event->isFullDay()
                        ? 0
                        : 1;
                }
            )
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Demande expirée
        |--------------------------------------------------------------------------
        |
        | On ne considère une demande expirée que s'il n'existe
        | aucun événement actuellement actif.
        |
        */
        if ($events->isEmpty()) {

            $expiredRequest = $agent->events->first(
                fn (HrEvent $event) =>
                    $event->status === 'demande'
                    && $event->date_fin
                    && $event->date_fin->lt($date)
            );

            if ($expiredRequest) {

                return [
                    'agent' =>
                        $agent,

                    'code' =>
                        'present',

                    'label' =>
                        'Présent',

                    'start' =>
                        null,

                    'end' =>
                        null,

                    'availability' =>
                        'Aucune indisponibilité active — demande expirée',

                    'assignment' =>
                        $this->getActiveAssignment(
                            $agent,
                            $date
                        ),

                    'event' =>
                        null,

                    'expired' =>
                        true,
                ];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Événement pleine journée
        |--------------------------------------------------------------------------
        */
        $fullDay = $events->first(
            fn (HrEvent $event) =>
                $event->isFullDay()
        );

        if ($fullDay) {

            return $this->eventSituation(
                $agent,
                $fullDay
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Événement partiel
        |--------------------------------------------------------------------------
        */
        $partialEvent = $events->first(
            fn (HrEvent $event) =>
                !$event->isFullDay()
                && $this->isApplicableDay(
                    $event,
                    $date
                )
        );

        if ($partialEvent) {

            return [
                'agent' =>
                    $agent,

                'code' =>
                    $partialEvent->type
                    . '_partielle',

                'label' =>
                    HrEvent::TYPES[
                        $partialEvent->type
                    ]
                    ?? $partialEvent->type,

                'start' =>
                    $partialEvent->date_debut,

                'end' =>
                    $partialEvent->date_fin,

                'time_start' =>
                    $partialEvent->heure_debut,

                'time_end' =>
                    $partialEvent->heure_fin,

                'availability' =>
                    sprintf(
                        '%s à %s',

                        substr(
                            (string)
                            $partialEvent->heure_debut,
                            0,
                            5
                        ),

                        substr(
                            (string)
                            $partialEvent->heure_fin,
                            0,
                            5
                        )
                    ),

                'assignment' =>
                    $this->getActiveAssignment(
                        $agent,
                        $date
                    ),

                'event' =>
                    $partialEvent,

                'expired' =>
                    false,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Affectation active
        |--------------------------------------------------------------------------
        */
        $assignment = $this->getActiveAssignment(
            $agent,
            $date
        );

        /*
        |--------------------------------------------------------------------------
        | PRÉSENT
        |--------------------------------------------------------------------------
        |
        | C'est ici le changement principal.
        |
        | Même si l'agent n'a aucune affectation,
        | il reste considéré comme présent lorsqu'il est actif
        | et qu'il n'a aucune indisponibilité.
        |
        */
        return [
            'agent' =>
                $agent,

            'code' =>
                'present',

            'label' =>
                'Présent',

            'start' =>
                $assignment?->date_debut,

            'end' =>
                $assignment?->date_fin,

            'availability' =>
                $assignment
                    ? (
                        $assignment->service
                        ?: $assignment->direction
                        ?: 'Direction'
                    )
                    : 'Direction — aucune affectation enregistrée',

            'assignment' =>
                $assignment,

            'event' =>
                null,

            'expired' =>
                false,
        ];
    }


    /**
     * Retourne l'affectation active d'un agent à une date donnée.
     */
    private function getActiveAssignment(
        HrAgent $agent,
        Carbon $date
    ) {
        return $agent->assignments
            ->filter(
                fn ($item) =>
                    $item->date_debut
                    && $item->date_debut->lte($date)
                    && (
                        !$item->date_fin
                        || $item->date_fin->gte($date)
                    )
            )
            ->sortByDesc(
                fn ($item) =>
                    $item->date_debut
                )
            ->first();
    }


    /**
     * Transforme un événement pleine journée
     * en situation.
     */
    private function eventSituation(
        HrAgent $agent,
        HrEvent $event
    ): array {

        return [
            'agent' =>
                $agent,

            'code' =>
                $event->type,

            'label' =>
                HrEvent::TYPES[
                    $event->type
                ]
                ?? $event->type,

            'start' =>
                $event->date_debut,

            'end' =>
                $event->date_fin,

            'time_start' =>
                $event->heure_debut,

            'time_end' =>
                $event->heure_fin,

            'availability' =>
                $event->title
                ?: $event->motif
                ?: 'Indisponible',

            'assignment' =>
                $this->getActiveAssignment(
                    $agent,
                    $event->date_debut
                ),

            'event' =>
                $event,

            'expired' =>
                $event->date_fin?->lt(today())
                === true,
        ];
    }


    /**
     * Vérifie si un événement est actif à une date donnée.
     */
    private function eventIsActiveOnDate(
        HrEvent $event,
        Carbon $date
    ): bool {

        if (!$event->date_debut) {
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Date de début
        |--------------------------------------------------------------------------
        */
        if ($event->date_debut->gt($date)) {
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Date de fin
        |--------------------------------------------------------------------------
        */
        if (
            $event->date_fin
            && $event->date_fin->lt($date)
        ) {
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Jours de la semaine
        |--------------------------------------------------------------------------
        */
        if (
            !$this->isApplicableDay(
                $event,
                $date
            )
        ) {
            return false;
        }

        return true;
    }


    /**
     * Vérifie si un événement s'applique
     * au jour sélectionné.
     */
    private function isApplicableDay(
        HrEvent $event,
        Carbon $date
    ): bool {

        if (!$event->jours_semaine) {
            return true;
        }

        /*
        |--------------------------------------------------------------------------
        | Carbon :
        |
        | 1 = lundi
        | 2 = mardi
        | 3 = mercredi
        | 4 = jeudi
        | 5 = vendredi
        | 6 = samedi
        | 7 = dimanche
        |--------------------------------------------------------------------------
        */
        return in_array(
            $date->dayOfWeekIso,
            $event->jours_semaine,
            true
        );
    }


    /**
     * Nombre de jours d'un événement.
     */
    public function eventDays(
        HrEvent $event
    ): int {

        if (!$event->date_debut) {
            return 0;
        }

        $end =
            $event->date_fin
            ?: $event->date_debut;

        return $end->diffInDays(
            $event->date_debut
        ) + 1;
    }


    /**
     * Événements validés d'un agent.
     */
    public function approvedEvents(
        HrAgent $agent,
        ?Carbon $from = null,
        ?Carbon $to = null
    ): Collection {

        return $agent->events
            ->filter(
                function (
                    HrEvent $event
                ) use (
                    $from,
                    $to
                ) {

                    if (
                        $event->status !== 'valide'
                    ) {
                        return false;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Événement terminé avant la période
                    |--------------------------------------------------------------------------
                    */
                    if (
                        $from
                        && $event->date_fin?->lt($from)
                    ) {
                        return false;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Événement commencé après la période
                    |--------------------------------------------------------------------------
                    */
                    if (
                        !$event->date_debut
                        || (
                            $to
                            && $event->date_debut->gt($to)
                        )
                    ) {
                        return false;
                    }

                    return true;
                }
            );
    }
}