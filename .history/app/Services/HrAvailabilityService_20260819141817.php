<?php

namespace App\Services;

use App\Models\HrAgent;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class HrAvailabilityService
{
    public function dashboard(?Carbon $date = null): array
    {
        $date ??= today();
        $agents = HrAgent::query()->where('actif', true)->with(['currentAssignment', 'assignments'])->orderBy('nom')->get();
        $situations = $agents->map(fn (HrAgent $agent) => $this->situation($agent, $date));

        return [
            'agents' => $agents,
            'situations' => $situations,
            'stats' => [
                'total' => $situations->count(),
                'present' => $situations->where('code', 'present')->count(),
                'affectation_temporaire' => $situations->where('code', 'affectation_temporaire')->count(),
                'sans_affectation' => $situations->where('code', 'sans_affectation')->count(),
            ],
        ];
    }

    public function situation(HrAgent $agent, Carbon $date): array
    {
        $assignment = $agent->assignments->first(function ($item) use ($date) {
            return $item->date_debut->lte($date) && (! $item->date_fin || $item->date_fin->gte($date));
        });

        if (! $assignment) {
            return ['agent' => $agent, 'code' => 'present', 'label' => 'Présent', 'start' => null, 'end' => null, 'availability' => 'Direction'];
        }

        $temporary = $assignment->date_fin !== null;
        return [
            'agent' => $agent,
            'code' => $temporary ? 'affectation_temporaire' : 'present',
            'label' => $temporary ? 'Affectation temporaire' : 'Présent',
            'start' => $assignment->date_debut,
            'end' => $assignment->date_fin,
            'availability' => $assignment->service ?: $assignment->direction ?: 'Direction',
            'assignment' => $assignment,
        ];
    }
}
