<?php

namespace App\Http\Controllers;

use App\Models\HrAgent;
use App\Models\HrDocumentSetting;
use App\Models\HrEvent;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html;

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

    public function preview(int $agent, string $document, ?int $event = null)
    {
        [$title, $filename, $agentModel, $eventModel] = $this->documentParts($agent, $document, $event);
        return $this->render($title, $filename, $agentModel, $eventModel, [], 'preview');
    }

    public function word(int $agent, string $document, ?int $event = null)
    {
        [$title, $filename, $agentModel, $eventModel] = $this->documentParts($agent, $document, $event);
        return $this->render($title, $filename, $agentModel, $eventModel, [], 'word');
    }

    private function eventDocument(int $agent, int $event, string $type, string $title, string $filename)
    {
        $agentModel = HrAgent::query()->findOrFail($agent);
        $eventModel = HrEvent::query()->where('agent_id', $agentModel->id)->where('id', $event)->where('type', $type)->firstOrFail();
        return $this->render($title, $filename, $agentModel, $eventModel);
    }

    private function documentParts(int $agent, string $document, ?int $event): array
    {
        $agentModel = HrAgent::query()->findOrFail($agent);
        $definitions = [
            'non-jouissance' => [null, 'Attestation de non-jouissance de congé', 'attestation-non-jouissance'],
            'conge' => ['conge', 'Fiche de congé', 'fiche-conge'],
            'absence' => ['autorisation_absence', 'Autorisation d’absence', 'autorisation-absence'],
            'mission' => ['mission', 'Ordre de mission', 'ordre-mission'],
            'formation' => ['formation', 'Autorisation de formation', 'autorisation-formation'],
        ];
        abort_unless(isset($definitions[$document]), 404);
        [$type, $title, $filename] = $definitions[$document];
        $eventModel = $type ? HrEvent::query()->where('agent_id', $agentModel->id)->whereKey($event)->where('type', $type)->firstOrFail() : null;
        return [$title, $filename, $agentModel, $eventModel];
    }

    private function render(string $title, string $filename, HrAgent $agent, ?HrEvent $event, array $extra = [], string $format = 'pdf')
    {
        $events = $agent->events()->get();
        $daysByType = $events->groupBy('type')->map(fn ($items) => $items->filter(fn ($item) => ! in_array($item->status, ['refuse', 'annule'], true))->sum(fn ($item) => $this->eventDays($item)));
        $daysSummary = [
            'by_type' => $daysByType,
            'absence' => (int) ($daysByType['autorisation_absence'] ?? 0),
            'conge' => (int) ($daysByType['conge'] ?? 0),
            'indisponibilite' => (int) $daysByType->sum(),
            'demandes' => $events->filter(fn ($item) => in_array($item->status, ['demande', 'valide', 'termine'], true))->count(),
        ];
        $data = array_merge([
            'title' => $title,
            'agent' => $agent,
            'event' => $event,
            'extra' => $extra,
            'today' => Carbon::today(),
            'settings' => HrDocumentSetting::query()->first(),
            'daysSummary' => $daysSummary,
            'period' => 'Année '.now()->year,
        ], $extra);

        if ($format === 'preview') {
            return view('hr.documents.administrative', $data + ['preview' => true]);
        }
        if ($format === 'word') {
            $word = new PhpWord();
            $section = $word->addSection(['marginTop' => 1100, 'marginBottom' => 1100, 'marginLeft' => 1300, 'marginRight' => 1100]);
            Html::addHtml($section, view('hr.documents.word', $data)->render(), false, false);
            $path = storage_path('app/'.$filename.'-'.($agent->matricule ?: $agent->id).'.docx');
            IOFactory::createWriter($word, 'Word2007')->save($path);
            return response()->download($path, basename($path), ['Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])->deleteFileAfterSend(true);
        }
        return Pdf::loadView('hr.documents.administrative', $data)
            ->setPaper('a4', 'portrait')
            ->download($filename.'-'.($agent->matricule ?: $agent->id).'.pdf');
    }

    private function eventDays(HrEvent $event): int
    {
        return ($event->date_fin ?: $event->date_debut)->diffInDays($event->date_debut) + 1;
    }
}
