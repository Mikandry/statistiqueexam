<?php

namespace App\Http\Controllers;

use App\Models\HrAgent;
use App\Models\HrDocumentSetting;
use App\Models\HrEvent;
use App\Services\HrAvailabilityService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html;

class HrDocumentController extends Controller
{
    public function leave(
        Request $request,
        int $agent,
        int $event
    ) {
        $agentModel = $this->authorizedAgent($request, $agent);

        $eventModel = HrEvent::query()
            ->where('agent_id', $agentModel->id)
            ->whereKey($event)
            ->where('type', 'conge')
            ->firstOrFail();

        return $this->render(
            'Fiche de congé',
            'fiche-conge',
            $agentModel,
            $eventModel
        );
    }

    public function nonLeave(
        Request $request,
        int $agent
    ) {
        $agentModel = $this->authorizedAgent(
            $request,
            $agent
        );

        $year = (int) $request->query(
            'year',
            now()->year
        );

        return $this->render(
            'Attestation de non-jouissance de congé',
            'attestation-non-jouissance',
            $agentModel,
            null,
            [
                'year' => $year,
                'period' => 'Année ' . $year,
            ]
        );
    }

    public function absence(
        Request $request,
        int $agent,
        int $event
    ) {
        $agentModel = $this->authorizedAgent(
            $request,
            $agent
        );

        $eventModel = HrEvent::query()
            ->where('agent_id', $agentModel->id)
            ->whereKey($event)
            ->where('type', 'autorisation_absence')
            ->firstOrFail();

        return $this->render(
            'Autorisation d’absence',
            'autorisation-absence',
            $agentModel,
            $eventModel
        );
    }

    public function mission(
        Request $request,
        int $agent,
        int $event
    ) {
        $agentModel = $this->authorizedAgent(
            $request,
            $agent
        );

        $eventModel = HrEvent::query()
            ->where('agent_id', $agentModel->id)
            ->whereKey($event)
            ->where('type', 'mission')
            ->firstOrFail();

        return $this->render(
            'Ordre de mission',
            'ordre-mission',
            $agentModel,
            $eventModel
        );
    }

    public function training(
        Request $request,
        int $agent,
        int $event
    ) {
        $agentModel = $this->authorizedAgent(
            $request,
            $agent
        );

        $eventModel = HrEvent::query()
            ->where('agent_id', $agentModel->id)
            ->whereKey($event)
            ->where('type', 'formation')
            ->firstOrFail();

        return $this->render(
            'Autorisation de formation',
            'autorisation-formation',
            $agentModel,
            $eventModel
        );
    }

    public function preview(
        Request $request,
        int $agent,
        string $document,
        ?int $event = null
    ) {
        [
            $title,
            $filename,
            $agentModel,
            $eventModel
        ] = $this->documentParts(
            $request,
            $agent,
            $document,
            $event
        );

        return $this->render(
            $title,
            $filename,
            $agentModel,
            $eventModel,
            [],
            'preview'
        );
    }

    public function word(
        Request $request,
        int $agent,
        string $document,
        ?int $event = null
    ) {
        [
            $title,
            $filename,
            $agentModel,
            $eventModel
        ] = $this->documentParts(
            $request,
            $agent,
            $document,
            $event
        );

        return $this->render(
            $title,
            $filename,
            $agentModel,
            $eventModel,
            [],
            'word'
        );
    }

    private function authorizedAgent(
        Request $request,
        int $agent
    ): HrAgent {
        $user = $request->user();

        if ($user?->isAdmin()) {
            return HrAgent::query()
                ->findOrFail($agent);
        }

        abort_unless(
            $user?->hrAgent?->id === $agent,
            403,
            'Vous ne pouvez accéder qu’à votre propre dossier.'
        );

        return $user->hrAgent;
    }

    private function documentParts(
        Request $request,
        int $agent,
        string $document,
        ?int $event
    ): array {
        $agentModel = $this->authorizedAgent(
            $request,
            $agent
        );

        $definitions = [
            'non-jouissance' => [
                null,
                'Attestation de non-jouissance de congé',
                'attestation-non-jouissance',
            ],

            'conge' => [
                'conge',
                'Fiche de congé',
                'fiche-conge',
            ],

            'absence' => [
                'autorisation_absence',
                'Autorisation d’absence',
                'autorisation-absence',
            ],

            'mission' => [
                'mission',
                'Ordre de mission',
                'ordre-mission',
            ],

            'formation' => [
                'formation',
                'Autorisation de formation',
                'autorisation-formation',
            ],
        ];

        abort_unless(
            isset($definitions[$document]),
            404
        );

        [
            $type,
            $title,
            $filename
        ] = $definitions[$document];

        $eventModel = null;

        if ($type) {
            $eventModel = HrEvent::query()
                ->where('agent_id', $agentModel->id)
                ->whereKey($event)
                ->where('type', $type)
                ->firstOrFail();
        }

        return [
            $title,
            $filename,
            $agentModel,
            $eventModel,
        ];
    }

    private function render(
        string $title,
        string $filename,
        HrAgent $agent,
        ?HrEvent $event,
        array $extra = [],
        string $format = 'pdf'
    ) {
        $availability = app(
            HrAvailabilityService::class
        );

        $events = $agent->events()->get();

        $daysByType = $events
            ->filter(
                fn ($item) =>
                    !in_array(
                        $item->status,
                        ['refuse', 'annule'],
                        true
                    )
            )
            ->groupBy('type')
            ->map(
                fn ($items) =>
                    $items->sum(
                        fn ($item) =>
                            $availability->eventDays($item)
                    )
            );

        $daysSummary = [
            'by_type' => $daysByType,

            'absence' =>
                (int) ($daysByType['autorisation_absence'] ?? 0),

            'conge' =>
                (int) ($daysByType['conge'] ?? 0),

            'indisponibilite' =>
                (int) $daysByType->sum(),

            'demandes' =>
                $events
                    ->filter(
                        fn ($item) =>
                            in_array(
                                $item->status,
                                [
                                    'demande',
                                    'valide',
                                    'termine'
                                ],
                                true
                            )
                    )
                    ->count(),
        ];

        $data = array_merge([
            'title' => $title,
            'agent' => $agent,
            'event' => $event,
            'extra' => $extra,
            'today' => Carbon::today(),
            'settings' =>
                HrDocumentSetting::query()->first(),
            'daysSummary' => $daysSummary,
            'period' => 'Année ' . now()->year,
        ], $extra);

        if ($format === 'preview') {
            return view(
                'hr.documents.administrative',
                $data + ['preview' => true]
            );
        }

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
                view(
                    'hr.documents.word',
                    $data
                )->render(),
                false,
                false
            );

            $path = storage_path(
                'app/' .
                $filename .
                '-' .
                ($agent->matricule ?: $agent->id) .
                '.docx'
            );

            IOFactory::createWriter(
                $word,
                'Word2007'
            )->save($path);

            return response()
                ->download(
                    $path,
                    basename($path),
                    [
                        'Content-Type' =>
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                    ]
                )
                ->deleteFileAfterSend(true);
        }

        return Pdf::loadView(
            'hr.documents.administrative',
            $data
        )
            ->setPaper('a4', 'portrait')
            ->download(
                $filename .
                '-' .
                ($agent->matricule ?: $agent->id) .
                '.pdf'
            );
    }
}