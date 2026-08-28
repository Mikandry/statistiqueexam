<?php

namespace App\Http\Controllers;

use App\Models\HrAgent;
use Illuminate\Http\Request;

class HrPersonnelController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $search = trim((string) $request->query('q', ''));

        $agents = HrAgent::query()
            ->where('actif', true)
            ->with([
                'user',
                'events',
                'assignments',
                'currentAssignment',
            ])
            ->when(
                $search !== '',
                function ($query) use ($search) {
                    $query->where(function ($sub) use ($search) {
                        $sub
                            ->where('nom', 'like', "%{$search}%")
                            ->orWhere(
                                'prenoms',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'matricule',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'fonction',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'service',
                                'like',
                                "%{$search}%"
                            );
                    });
                }
            )
            ->orderBy('nom')
            ->paginate(20)
            ->withQueryString();

        return view(
            'hr.agents.list',
            compact('agents', 'search')
        );
    }

    public function show(Request $request, int $agent)
    {
        $user = $request->user();
        $agentModel = HrAgent::query()->findOrFail($agent);

        abort_unless(
            $user->isAdmin() || $user->hr_agent_id === $agentModel->id,
            403
        );

        $agentModel->load([
            'user',
            'events',
            'assignments',
            'currentAssignment',
        ]);

        return view(
            'hr.agents.show',
            [
                'agent' => $agentModel,
                'isAdmin' => $user->isAdmin(),
            ]
        );
    }

    public function myProfile(Request $request)
    {
        $agent = $request->user()->hrAgent;

        abort_unless($agent, 403, 'Votre compte n’est pas associé à un dossier du personnel.');

        return $this->show($request, $agent->id);
    }
}