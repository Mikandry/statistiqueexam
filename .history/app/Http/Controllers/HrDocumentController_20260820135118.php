<?php

namespace App\Http\Controllers;

use App\Models\HrAgent;
use App\Models\HrDocumentSetting;
use App\Models\HrEvent;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HrDocumentController extends Controller
{
    public function leave(int $agent, int $event)
    {
        return $this->eventDocument($agent, $event, 'conge', 'Fiche de congé', 'fiche-conge');
    }

    public function nonLeave(int $agent, Request $request)
    {
        $agentModel = HrAgent::query()->findOrFail($agent);
        $year = (int) $request->query('year', now()->year);
        return $this->render('Attestation de non-jouissance de congé', 'attestation-non-jouissance', $agentModel, null, [
            'year' => $year,
            'period' => 'Année '.$year,
        ]);
    }

    public function absence(int $agent, int $event)
    {
        return $this->eventDocument($agent, $event, 'autorisation_absence', 'Autorisation d’absence', 'autorisation-absence');
    }

    public function mission(int $agent, int $event)
    {
        return $this->eventDocument($agent, $event, 'mission', 'Ordre de mission', 'ordre-mission');
    }

    public function training(int $agent, int $event)
    {
        return $this->eventDocument($agent, $event, 'formation', 'Autorisation de formation', 'autorisation-formation');
    }

    private function eventDocument(int $agent, int $event, string $type, string $title, string $filename)
    {
        $agentModel = HrAgent::query()->findOrFail($agent);
        $eventModel = HrEvent::query()->where('agent_id', $agentModel->id)->where('id', $event)->where('type', $type)->firstOrFail();
        return $this->render($title, $filename, $agentModel, $eventModel);
    }

    private function render(string $title, string $filename, HrAgent $agent, ?HrEvent $event, array $extra = [])
    {
        $data = array_merge([
            'title' => $title,
            'agent' => $agent,
            'event' => $event,
            'extra' => $extra,
            'today' => Carbon::today(),
            'settings' => HrDocumentSetting::query()->first(),
        ], $extra);

        return Pdf::loadView('hr.documents.administrative', $data)
            ->setPaper('a4', 'portrait')
            ->download($filename.'-'.($agent->matricule ?: $agent->id).'.pdf');
    }
}
