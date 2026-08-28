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
    /*
    |--------------------------------------------------------------------------
    | HUB DOCUMENTS (aperçu unique pour toutes les attestations)
    |--------------------------------------------------------------------------
    */

    public function index(Request $request, int $agent)
    {
        $agentModel = $this->authorizedAgent($request, $agent);

        $settings = HrDocumentSetting::query()->first();

        abort_unless($settings, 500, 'Les paramètres des documents administratifs ne sont pas configurés.');

        $documents = [
            'non-interruption' => [
                'title' => 'Attestation de non interruption de service',
                'icon' => '🔄',
                'color' => 'from-cyan-500 to-teal-600',
                'description' => 'Atteste la continuité de service depuis l’entrée dans l’administration.',
            ],
            'non-jouissance' => [
                'title' => 'Attestation de non-jouissance de congé',
                'icon' => '📄',
                'color' => 'from-slate-700 to-slate-900',
                'description' => 'Atteste que l’agent n’a pas bénéficié de congé durant l’année.',
            ],
            'prise-service' => [
                'title' => 'Fiche de prise de service',
                'icon' => '🚀',
                'color' => 'from-emerald-500 to-green-600',
                'description' => 'Certifie la date effective de prise de service de l’agent.',
            ],
            'conge' => [
                'title' => 'Fiche de congé',
                'icon' => '🌴',
                'color' => 'from-blue-500 to-indigo-600',
                'description' => 'Autorisation de congé liée à un événement validé.',
                'requires_event' => true,
            ],
            'absence' => [
                'title' => 'Autorisation d’absence',
                'icon' => '📝',
                'color' => 'from-orange-500 to-amber-600',
                'description' => 'Autorisation d’absence liée à un événement validé.',
                'requires_event' => true,
            ],
            'mission' => [
                'title' => 'Ordre de mission',
                'icon' => '📋',
                'color' => 'from-violet-500 to-purple-600',
                'description' => 'Ordre de mission lié à un événement validé.',
                'requires_event' => true,
            ],
            'formation' => [
                'title' => 'Demande de formation',
                'icon' => '📚',
                'color' => 'from-pink-500 to-rose-600',
                'description' => 'Demande de formation liée à un événement validé.',
                'requires_event' => true,
            ],
            'autre' => [
                'title' => 'Autre demande administrative',
                'icon' => '🗂️',
                'color' => 'from-slate-500 to-slate-700',
                'description' => 'Demande administrative diverses.',
                'requires_event' => true,
            ],
            'fiche-administrative' => [
                'title' => 'Fiche administrative',
                'icon' => '🗃️',
                'color' => 'from-teal-500 to-emerald-600',
                'description' => 'Récapitulatif complet de la situation administrative de l’agent.',
            ],
        ];

        // Récupération des événements validés pour les documents qui en nécessitent
        $validatedEvents = $agentModel->events()
            ->where('status', 'valide')
            ->orderByDesc('date_debut')
            ->get()
            ->groupBy('type');

        $events = [
            'conge' => $validatedEvents->get('conge', collect())->values(),
            'absence' => $validatedEvents->get('autorisation_absence', collect())->values(),
            'mission' => $validatedEvents->get('mission', collect())->values(),
            'formation' => $validatedEvents->get('formation', collect())->values(),
            'autre' => $validatedEvents->get('autre', collect())->values(),
        ];

        return view('hr.documents.index', [
            'agent' => $agentModel,
            'settings' => $settings,
            'documents' => $documents,
            'events' => $events,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | NON INTERRUPTION DE SERVICE
    |--------------------------------------------------------------------------
    */

    public function nonInterruption(Request $request, int $agent)
    {
        $agentModel = $this->authorizedAgent($request, $agent);

        return $this->renderDocument(
            agent: $agentModel,
            document: 'non-interruption',
            request: $request
        );
    }

    public function nonLeave(Request $request, int $agent)
    {
        $agentModel = $this->authorizedAgent($request, $agent);

        $year = (int) $request->query('year', now()->year);

        return $this->renderDocument(
            agent: $agentModel,
            document: 'non-jouissance',
            request: $request,
            extra: [
                'year' => $year,
                'period' => 'Année ' . $year,
            ]
        );
    }

    public function serviceStart(Request $request, int $agent)
    {
        $agentModel = $this->authorizedAgent($request, $agent);

        return $this->renderDocument(
            agent: $agentModel,
            document: 'prise-service',
            request: $request
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CONGE
    |--------------------------------------------------------------------------
    */

    public function leave(Request $request, int $agent, int $event)
    {
        $agentModel = $this->authorizedAgent($request, $agent);

        $eventModel = HrEvent::query()
            ->where('agent_id', $agentModel->id)
            ->whereKey($event)
            ->where('type', 'conge')
            ->firstOrFail();

        abort_unless(
            $request->user()?->isAdmin() || $eventModel->status === 'valide',
            403,
            'Le document sera disponible après validation de la demande.'
        );

        return $this->renderDocument(
            agent: $agentModel,
            document: 'conge',
            event: $eventModel
        );
    }

    /*
    |--------------------------------------------------------------------------
    | AUTORISATION D'ABSENCE
    |--------------------------------------------------------------------------
    */

    public function absence(Request $request, int $agent, int $event)
    {
        $agentModel = $this->authorizedAgent($request, $agent);

        $eventModel = HrEvent::query()
            ->where('agent_id', $agentModel->id)
            ->whereKey($event)
            ->where('type', 'autorisation_absence')
            ->firstOrFail();

        abort_unless(
            $request->user()?->isAdmin() || $eventModel->status === 'valide',
            403,
            'Le document sera disponible après validation de la demande.'
        );

        return $this->renderDocument(
            agent: $agentModel,
            document: 'absence',
            event: $eventModel
        );
    }

    public function mission(Request $request, int $agent, int $event)
    {
        $agentModel = $this->authorizedAgent($request, $agent);

        $eventModel = HrEvent::query()
            ->where('agent_id', $agentModel->id)
            ->whereKey($event)
            ->where('type', 'mission')
            ->firstOrFail();

        return $this->renderDocument(
            agent: $agentModel,
            document: 'mission',
            event: $eventModel
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DEMANDE DE FORMATION
    |--------------------------------------------------------------------------
    */

    public function training(Request $request, int $agent, int $event)
    {
        $agentModel = $this->authorizedAgent($request, $agent);

        $eventModel = HrEvent::query()
            ->where('agent_id', $agentModel->id)
            ->whereKey($event)
            ->where('type', 'formation')
            ->firstOrFail();

        return $this->renderDocument(
            agent: $agentModel,
            document: 'formation',
            event: $eventModel
        );
    }

    /*
    |--------------------------------------------------------------------------
    | AUTRE DEMANDE
    |--------------------------------------------------------------------------
    */

    public function other(Request $request, int $agent, ?int $event = null)
    {
        $agentModel = $this->authorizedAgent($request, $agent);

        $eventModel = null;

        if ($event) {
            $eventModel = HrEvent::query()
                ->where('agent_id', $agentModel->id)
                ->whereKey($event)
                ->firstOrFail();
        }

        return $this->renderDocument(
            agent: $agentModel,
            document: 'autre',
            event: $eventModel
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FICHE ADMINISTRATIVE
    |--------------------------------------------------------------------------
    */

    public function administrative(Request $request, int $agent)
    {
        $agentModel = $this->authorizedAgent($request, $agent);

        $agentModel->load([
            'assignments',
            'events',
        ]);

        return $this->renderDocument(
            agent: $agentModel,
            document: 'fiche-administrative'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | APERCU
    |--------------------------------------------------------------------------
    */

    public function preview(
        Request $request,
        int $agent,
        string $document,
        ?int $event = null
    ) {
        $agentModel = $this->authorizedAgent($request, $agent);

        $eventModel = $this->findEvent(
            $agentModel,
            $document,
            $event
        );

        return $this->renderDocument(
            agent: $agentModel,
            document: $document,
            event: $eventModel,
            format: 'preview'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | WORD
    |--------------------------------------------------------------------------
    */

    public function word(
        Request $request,
        int $agent,
        string $document,
        ?int $event = null
    ) {
        $agentModel = $this->authorizedAgent($request, $agent);

        $eventModel = $this->findEvent(
            $agentModel,
            $document,
            $event
        );

        return $this->renderDocument(
            agent: $agentModel,
            document: $document,
            event: $eventModel,
            format: 'word'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PDF
    |--------------------------------------------------------------------------
    */

    public function pdf(
        Request $request,
        int $agent,
        string $document,
        ?int $event = null
    ) {
        $agentModel = $this->authorizedAgent($request, $agent);

        $eventModel = $this->findEvent(
            $agentModel,
            $document,
            $event
        );

        return $this->renderDocument(
            agent: $agentModel,
            document: $document,
            event: $eventModel,
            format: 'pdf'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FIND EVENT
    |--------------------------------------------------------------------------
    */

    private function authorizedAgent(Request $request, int $agent): HrAgent
    {
        $agentModel = HrAgent::query()->findOrFail($agent);

        abort_unless(
            $request->user()?->isAdmin()
                || $request->user()?->hr_agent_id === $agentModel->id,
            403
        );

        return $agentModel;
    }

    private function findEvent(
        HrAgent $agent,
        string $document,
        ?int $event
    ): ?HrEvent {

        $types = [
            'conge' => 'conge',
            'absence' => 'autorisation_absence',
            'formation' => 'formation',
            'mission' => 'mission',
            'autre' => 'autre',
        ];

        if (!isset($types[$document]) || !$event) {
            return null;
        }

        return HrEvent::query()
            ->where('agent_id', $agent->id)
            ->whereKey($event)
            ->where('type', $types[$document])
            ->firstOrFail();
    }

    /*
    |--------------------------------------------------------------------------
    | RENDER DOCUMENT
    |--------------------------------------------------------------------------
    */

    private function renderDocument(
        HrAgent $agent,
        string $document,
        Request $request = null,
        ?HrEvent $event = null,
        array $extra = [],
        string $format = 'pdf'
    ) {

        $settings = HrDocumentSetting::query()->first();

        abort_unless($settings, 500, 'Les paramètres des documents administratifs ne sont pas configurés.');

        $definitions = [

            'non-jouissance' => [
                'title' => 'ATTESTATION DE NON-JOUISSANCE DE CONGE',
                'filename' => 'attestation-non-jouissance',
            ],

            'non-interruption' => [
                'title' => 'ATTESTATION DE NON INTERRUPTION DE SERVICE',
                'filename' => 'attestation-non-interruption',
            ],

            'prise-service' => [
                'title' => 'FICHE DE PRISE DE SERVICE',
                'filename' => 'fiche-prise-service',
            ],

            'conge' => [
                'title' => 'FICHE DE CONGE',
                'filename' => 'fiche-conge',
            ],

            'absence' => [
                'title' => 'AUTORISATION D’ABSENCE',
                'filename' => 'autorisation-absence',
            ],

            'formation' => [
                'title' => 'DEMANDE DE FORMATION',
                'filename' => 'demande-formation',
            ],

            'mission' => [
                'title' => 'ORDRE DE MISSION',
                'filename' => 'ordre-mission',
            ],

            'autre' => [
                'title' => 'DEMANDE ADMINISTRATIVE',
                'filename' => 'demande-administrative',
            ],

            'fiche-administrative' => [
                'title' => 'FICHE ADMINISTRATIVE',
                'filename' => 'fiche-administrative',
            ],
        ];

        abort_unless(
            isset($definitions[$document]),
            404
        );

        $definition = $definitions[$document];

        $today = Carbon::today();

        $data = [
            'agent' => $agent,
            'event' => $event,
            'settings' => $settings,
            'document' => $document,
            'title' => $definition['title'],
            'today' => $today,
            'reference' => $this->reference($settings),
        ];

        $data = array_merge($data, $extra);

        /*
        |--------------------------------------------------------------------------
        | PREVIEW
        |--------------------------------------------------------------------------
        */

        if ($format === 'preview') {
            return view(
                'hr.documents.administrative',
                $data
            );
        }

        /*
        |--------------------------------------------------------------------------
        | WORD
        |--------------------------------------------------------------------------
        */

        if ($format === 'word') {

            $word = new PhpWord();

            $section = $word->addSection([
                'marginTop' => 1100,
                'marginBottom' => 1100,
                'marginLeft' => 1300,
                'marginRight' => 1100,
            ]);

            Html::addHtml(
                $section,
                view('hr.documents.word', $data)->render(),
                false,
                false
            );

            $filename =
                $definition['filename']
                . '-'
                . ($agent->matricule ?: $agent->id)
                . '.docx';

            $path = storage_path(
                'app/' . $filename
            );

            IOFactory::createWriter(
                $word,
                'Word2007'
            )->save($path);

            return response()
                ->download(
                    $path,
                    $filename,
                    [
                        'Content-Type' =>
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                    ]
                )
                ->deleteFileAfterSend(true);
        }

        /*
        |--------------------------------------------------------------------------
        | PDF
        |--------------------------------------------------------------------------
        */

        return Pdf::loadView(
            'hr.documents.administrative',
            $data
        )
            ->setPaper('a4', 'portrait')
            ->download(
                $definition['filename']
                . '-'
                . ($agent->matricule ?: $agent->id)
                . '.pdf'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | REFERENCE
    |--------------------------------------------------------------------------
    */

    private function reference(
        HrDocumentSetting $settings
    ): string {

        $year = $settings->reference_year ?: now()->year;

       
        return('N°' .$settings->next_reference_number ?: 1)
        . ' '
        . '/'
        . $year
         . ' '
       . ($settings->reference_prefix ?: 'N°');
    }
}