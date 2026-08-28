<?php

namespace App\Http\Controllers;

use App\Models\HrEvent;
use App\Services\HrAvailabilityService;
use Illuminate\Http\Request;

class HrDashboardController extends Controller
{
    public function __construct(
        private readonly HrAvailabilityService $availability
    ) {
    }

    public function index(Request $request)
    {
        $user = $request->user();

        /*
         * Administrator sees everyone.
         */
        if ($user->isAdmin()) {
            $data = $this->availability->dashboard(
                $request->date('date') ?: today()
            );

            $search = trim((string) $request->query('q', ''));

            if ($search !== '') {
                $needle = mb_strtolower($search);

                $data['situations'] = $data['situations']
                    ->filter(function ($s) use ($needle) {
                        return str_contains(
                            mb_strtolower($s['agent']->full_name),
                            $needle
                        )
                        || str_contains(
                            mb_strtolower(
                                (string) $s['agent']->matricule
                            ),
                            $needle
                        );
                    })
                    ->values();
            }

            return view('hr.dashboard', $data + [
                'selectedDate' =>
                    $request->query(
                        'date',
                        today()->toDateString()
                    ),

                'search' => $search,

                'recentEvents' =>
                    HrEvent::query()
                        ->with('agent')
                        ->latest()
                        ->limit(12)
                        ->get(),

                'isAdmin' => true,
            ]);
        }

        /*
         * Ordinary user sees only his own personnel record.
         */
        $agent = $user->hrAgent;

        abort_unless($agent, 403,
            'Votre compte utilisateur n’est pas encore associé à un dossier du personnel.'
        );

        $data = $this->availability->dashboard(
            $request->date('date') ?: today(),
            $agent
        );

        return view('hr.dashboard', $data + [
            'selectedDate' =>
                $request->query(
                    'date',
                    today()->toDateString()
                ),

            'search' => '',

            'recentEvents' =>
                $agent->events()
                    ->latest('date_debut')
                    ->limit(12)
                    ->get(),

            'isAdmin' => false,
        ]);
    }
}