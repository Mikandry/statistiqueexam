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
        abort_unless($request->user()->isAdmin(), 403);

        $agentModel = HrAgent::query()
            ->with([
                'user',
                'events',
                'assignments',
                'currentAssignment',
            ])
            ->findOrFail($agent);

        return view(
            'hr.agents.show',
            [
                'agent' => $agentModel,
            ]
        );
    }
}