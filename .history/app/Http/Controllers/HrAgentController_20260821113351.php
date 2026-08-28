<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\HrAgent;
use App\Models\HrAssignment;
use App\Models\HrEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HrAgentController extends Controller
{
    private function admin(Request $request): void
    {
        abort_unless($request->user()?->isAdmin(), 403);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->admin($request);

        $agent = HrAgent::query()->create(
            $this->agentData($request)
        );

        AuditLog::record(
            $request,
            'hr_agent_created',
            ['agent_id' => $agent->id]
        );

        return back()->with(
            'success',
            'Agent ajouté au personnel.'
        );
    }

    public function update(
        Request $request,
        int $agent
    ): RedirectResponse {
        $this->admin($request);

        $agentModel = HrAgent::query()->findOrFail($agent);

        $agentModel->update(
            $this->agentData($request, $agentModel)
        );

        AuditLog::record(
            $request,
            'hr_agent_updated',
            ['agent_id' => $agentModel->id]
        );

        return back()->with(
            'success',
            'Dossier agent mis à jour.'
        );
    }

    public function storeAssignment(
        Request $request,
        int $agent
    ): RedirectResponse {
        $this->admin($request);

        $agentModel = HrAgent::query()->findOrFail($agent);

        $data = $request->validate([
            'direction' => ['nullable', 'string', 'max:255'],
            'service' => ['nullable', 'string', 'max:255'],
            'bureau' => ['nullable', 'string', 'max:255'],
            'fonction' => ['nullable', 'string', 'max:255'],

            'date_debut' => ['required', 'date'],

            'date_fin' => [
                'nullable',
                'date',
                'after_or_equal:date_debut',
            ],

            'motif' => ['nullable', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:255'],
        ]);

        /*
         * Historical assignments are preserved.
         */
        HrAssignment::query()
            ->where('agent_id', $agentModel->id)
            ->where('current', true)
            ->update([
                'current' => false,
            ]);

        $assignment = $agentModel->assignments()->create(
            $data + [
                'current' => true,
                'created_by' => $request->user()?->id,
            ]
        );

        /*
         * Keep the current snapshot on HrAgent.
         */
        $agentModel->update(
            array_filter(
                [
                    'direction' => $data['direction'] ?? null,
                    'service' => $data['service'] ?? null,
                    'bureau' => $data['bureau'] ?? null,
                    'fonction' => $data['fonction'] ?? null,
                ],
                fn ($value) => $value !== null
            )
        );

        AuditLog::record(
            $request,
            'hr_assignment_created',
            [
                'assignment_id' => $assignment->id,
                'agent_id' => $agentModel->id,
            ]
        );

        return back()->with(
            'success',
            'Affectation enregistrée.'
        );
    }

    public function storeEvent(
        Request $request,
        int $agent
    ): RedirectResponse {
        $this->admin($request);

        $agentModel = HrAgent::query()->findOrFail($agent);

        $data = $request->validate([
            'type' => [
                'required',
                Rule::in(array_keys(HrEvent::TYPES)),
            ],

            'status' => [
                'required',
                Rule::in(array_keys(HrEvent::STATUSES)),
            ],

            'title' => ['nullable', 'string', 'max:255'],
            'motif' => ['nullable', 'string'],

            'date_debut' => ['required', 'date'],

            'date_fin' => [
                'nullable',
                'date',
                'after_or_equal:date_debut',
            ],

            'heure_debut' => [
                'nullable',
                'date_format:H:i',
                'required_with:heure_fin',
            ],

            'heure_fin' => [
                'nullable',
                'date_format:H:i',
                'after:heure_debut',
                'required_with:heure_debut',
            ],

            'duree_heures' => [
                'nullable',
                'numeric',
                'min:0.25',
                'max:24',
            ],

            'jours_semaine' => [
                'nullable',
                'array',
            ],

            'jours_semaine.*' => [
                'integer',
                'between:1,7',
            ],

            'lieu' => ['nullable', 'string', 'max:255'],
            'organisme' => ['nullable', 'string', 'max:255'],
            'destination' => ['nullable', 'string', 'max:255'],

            'reference' => ['nullable', 'string', 'max:255'],
            'autorite' => ['nullable', 'string', 'max:255'],
            'observation' => ['nullable', 'string'],
        ]);

        /*
         * Prevent contradictory full-day situations.
         */
        if (
            ($data['status'] ?? 'valide') === 'valide'
            && empty($data['heure_debut'])
        ) {
            $conflict = $agentModel
                ->events()
                ->where('status', 'valide')
                ->whereDate(
                    'date_debut',
                    '<=',
                    $data['date_fin'] ?? $data['date_debut']
                )
                ->where(function ($query) use ($data) {
                    $query
                        ->whereNull('date_fin')
                        ->orWhereDate(
                            'date_fin',
                            '>=',
                            $data['date_debut']
                        );
                })
                ->exists();

            if ($conflict) {
                return back()
                    ->withErrors([
                        'date_debut' =>
                            'Une autre situation validée existe déjà pendant cette période pour cet agent.',
                    ])
                    ->withInput();
            }
        }

        $event = $agentModel->events()->create(
            $data + [
                'created_by' => $request->user()?->id,
            ]
        );

        AuditLog::record(
            $request,
            'hr_event_created',
            [
                'event_id' => $event->id,
                'agent_id' => $agentModel->id,
                'type' => $event->type,
            ]
        );

        return back()->with(
            'success',
            'Situation de l’agent enregistrée.'
        );
    }

   private function agentData(Request $request, ?HrAgent $agent = null): array
{
    return $request->validate([
        'matricule' => [
            'nullable',
            'string',
            'max:100',
            'unique:hr_agents,matricule,' . ($agent?->id ?? 'NULL')
        ],

        'nom' => ['required', 'string', 'max:255'],
        'prenoms' => ['nullable', 'string', 'max:255'],

        'sexe' => ['nullable', 'string', 'max:20'],
        'date_naissance' => ['nullable', 'date'],

        'cin' => ['nullable', 'string', 'max:100'],
        'telephone' => ['nullable', 'string', 'max:100'],
        'email' => ['nullable', 'email', 'max:255'],
        'adresse' => ['nullable', 'string'],

        /*
        |--------------------------------------------------------------------------
        | Situation administrative
        |--------------------------------------------------------------------------
        */

        'statut' => ['nullable', 'string', 'max:255'],
        'corps' => ['nullable', 'string', 'max:255'],
        'grade' => ['nullable', 'string', 'max:255'],
        'indice' => ['nullable', 'string', 'max:100'],
        'categorie' => ['nullable', 'string', 'max:255'],
        'echelon' => ['nullable', 'string', 'max:255'],

        /*
        |--------------------------------------------------------------------------
        | Affectation
        |--------------------------------------------------------------------------
        */

        'fonction' => ['nullable', 'string', 'max:255'],
        'direction' => ['nullable', 'string', 'max:255'],
        'service' => ['nullable', 'string', 'max:255'],
        'bureau' => ['nullable', 'string', 'max:255'],
        'superieur_hierarchique' => ['nullable', 'string', 'max:255'],

        /*
        |--------------------------------------------------------------------------
        | Informations budgétaires
        |--------------------------------------------------------------------------
        */

        'budget' => ['nullable', 'string', 'max:255'],
        'chapitre' => ['nullable', 'string', 'max:255'],

        /*
        |--------------------------------------------------------------------------
        | Dates
        |--------------------------------------------------------------------------
        */

        'date_recrutement' => ['nullable', 'date'],
        'date_prise_service' => ['nullable', 'date'],

        'situation_administrative' => [
            'nullable',
            'string',
            'max:255'
        ],

        'actif' => ['nullable', 'boolean'],
    ]);
}
}