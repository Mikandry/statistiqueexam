<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\HrAgent;
use App\Models\HrAssignment;
use App\Models\HrEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HrAgentController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $agent = HrAgent::query()->create($this->agentData($request));
        AuditLog::record($request, 'hr_agent_created', ['agent_id' => $agent->id]);
        return back()->with('success', 'Agent ajouté au personnel.');
    }

    public function update(Request $request, int $agent): RedirectResponse
    {
        $agent = HrAgent::query()->findOrFail($agent);
        $agent->update($this->agentData($request, $agent));
        AuditLog::record($request, 'hr_agent_updated', ['agent_id' => $agent->id]);
        return back()->with('success', 'Dossier agent mis à jour.');
    }

    public function storeAssignment(Request $request, int $agent): RedirectResponse
    {
        $agent = HrAgent::query()->findOrFail($agent);
        $data = $request->validate([
            'direction' => ['nullable', 'string', 'max:255'], 'service' => ['nullable', 'string', 'max:255'],
            'bureau' => ['nullable', 'string', 'max:255'], 'fonction' => ['nullable', 'string', 'max:255'],
            'date_debut' => ['required', 'date'], 'date_fin' => ['nullable', 'date', 'after_or_equal:date_debut'],
            'motif' => ['nullable', 'string', 'max:255'], 'reference' => ['nullable', 'string', 'max:255'],
        ]);
        HrAssignment::query()->where('agent_id', $agent->id)->update(['current' => false]);
        $assignment = $agent->assignments()->create($data + ['current' => true, 'created_by' => $request->user()?->id]);
        $agent->update(array_filter(['direction' => $data['direction'] ?? null, 'service' => $data['service'] ?? null, 'bureau' => $data['bureau'] ?? null, 'fonction' => $data['fonction'] ?? null], fn ($value) => $value !== null));
        AuditLog::record($request, 'hr_assignment_created', ['assignment_id' => $assignment->id, 'agent_id' => $agent->id]);
        return back()->with('success', 'Affectation enregistrée dans l’historique.');
    }

    public function storeEvent(Request $request, int $agent): RedirectResponse
    {
        $agentModel = HrAgent::query()->findOrFail($agent);
        $data = $request->validate([
            'type' => ['required', 'in:'.implode(',', array_keys(HrEvent::TYPES))],
            'status' => ['required', 'in:brouillon,demande,valide,refuse,annule,termine'],
            'title' => ['nullable', 'string', 'max:255'], 'motif' => ['nullable', 'string'],
            'date_debut' => ['required', 'date'], 'date_fin' => ['nullable', 'date', 'after_or_equal:date_debut'],
            'reference' => ['nullable', 'string', 'max:255'], 'autorite' => ['nullable', 'string', 'max:255'],
            'observation' => ['nullable', 'string'],
        ]);
        $event = $agentModel->events()->create($data + ['created_by' => $request->user()?->id]);
        AuditLog::record($request, 'hr_event_created', ['event_id' => $event->id, 'agent_id' => $agentModel->id, 'type' => $event->type]);
        return back()->with('success', 'Situation de l’agent enregistrée.');
    }

    private function agentData(Request $request, ?HrAgent $agent = null): array
    {
        return $request->validate([
            'matricule' => ['nullable', 'string', 'max:100', 'unique:hr_agents,matricule,'.($agent?->id ?? 'NULL')],
            'nom' => ['required', 'string', 'max:255'], 'prenoms' => ['nullable', 'string', 'max:255'],
            'sexe' => ['nullable', 'string', 'max:20'], 'date_naissance' => ['nullable', 'date'],
            'cin' => ['nullable', 'string', 'max:100'], 'telephone' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'], 'adresse' => ['nullable', 'string'],
            'statut' => ['nullable', 'string', 'max:255'], 'corps' => ['nullable', 'string', 'max:255'],
            'grade' => ['nullable', 'string', 'max:255'], 'indice' => ['nullable', 'string', 'max:100'], 'categorie' => ['nullable', 'string', 'max:255'],
            'echelon' => ['nullable', 'string', 'max:255'], 'fonction' => ['nullable', 'string', 'max:255'],
            'date_recrutement' => ['nullable', 'date'], 'date_prise_service' => ['nullable', 'date'],
            'direction' => ['nullable', 'string', 'max:255'], 'service' => ['nullable', 'string', 'max:255'],
            'bureau' => ['nullable', 'string', 'max:255'], 'superieur_hierarchique' => ['nullable', 'string', 'max:255'],
            'situation_administrative' => ['nullable', 'string', 'max:255'], 'actif' => ['nullable', 'boolean'],
        ]);
    }
}
