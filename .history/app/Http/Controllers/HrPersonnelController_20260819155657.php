<?php

namespace App\Http\Controllers;

use App\Models\HrAgent;
use Illuminate\Http\Request;

class HrPersonnelController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $agents = HrAgent::query()->where('actif', true)->with(['events', 'assignments'])->when($search !== '', fn ($query) => $query->where(function ($sub) use ($search) { $sub->where('nom', 'like', "%{$search}%")->orWhere('prenoms', 'like', "%{$search}%")->orWhere('matricule', 'like', "%{$search}%")->orWhere('fonction', 'like', "%{$search}%")->orWhere('service', 'like', "%{$search}%"); }))->orderBy('nom')->paginate(20)->withQueryString();
        return view('hr.agents.index', compact('agents', 'search'));
    }
}
