// ... existing code ...

namespace App\Http\Controllers;

use App\Models\Centre;
use App\Models\Cisco;
use App\Models\Dren;
use App\Models\Examen;
use App\Models\AffectationAgent;
use App\Services\VacationCalculationService;
use Illuminate\Http\Request;

class VacationController extends Controller
{
    protected VacationCalculationService $vacationService;

    public function __construct(VacationCalculationService $vacationService)
    {
        $this->vacationService = $vacationService;
    }

    // ... existing code ...

    /**
     * Tableau de bord principal Vacation 2026.
     */
    public function dashboard(Request $request)
    {
        $examenId = $request->get('examen_id');
        $drenId = $request->get('dren_id');
        $ciscoId = $request->get('cisco_id');
        $centreId = $request->get('centre_id');
        $typeCentre = $request->get('type_centre');
        $phaseFilter = $request->get('phase');

        $query = Centre::with(['candidats', 'salles', 'cisco.dren', 'examen']);

        if ($examenId) $query->where('examen_id', $examenId);
        if ($drenId) $query->whereHas('cisco', fn($q) => $q->where('dren_id', $drenId));
        if ($ciscoId) $query->where('cisco_id', $ciscoId);
        if ($centreId) $query->where('id', $centreId);
        if ($typeCentre) $query->where('type_centre', $typeCentre);

        $centres = $query->get();

        $totalCandidats = 0;
        $totalSalles = 0;
        $totalAgentsEstimes = 0;
        $montantEstimatifTotal = 0;
        $detailsCentres = [];

        foreach ($centres as $centre) {
            $calc = $this->vacationService->calculateCentreVacation($centre);
            
            if ($phaseFilter) {
                $calc['activites'] = array_filter($calc['activites'], fn($act) => $act['phase'] === $phaseFilter);
                $calc['montant_estime'] = array_reduce($calc['activites'], fn($s, $a) => $s + $a['montant_estime'], 0);
                $calc['total_agents_estimes'] = array_reduce($calc['activites'], fn($s, $a) => $s + $a['nombre_agents'], 0);
            }

            $totalCandidats += $calc['candidats'];
            $totalSalles += $calc['salles'];
            $totalAgentsEstimes += $calc['total_agents_estimes'];
            $montantEstimatifTotal += $calc['montant_estime'];
            $detailsCentres[] = $calc;
        }

        // Suivi réel des affectations et paiements (Sans confondre avec l'estimation)
        $totalAgentsAffectes = AffectationAgent::when($examenId, fn($q) => $q->where('examen_id', $examenId))->count();
        $ecartAgents = $totalAgentsEstimes - $totalAgentsAffectes;

        $montantValide = AffectationAgent::when($examenId, fn($q) => $q->where('examen_id', $examenId))
            ->where('statut', 'VALIDE')
            ->sum('montant');

        $montantPaye = AffectationAgent::when($examenId, fn($q) => $q->where('examen_id', $examenId))
            ->where('statut', 'PAYE')
            ->sum('montant');

        return view('vacation.dashboard', [
            'totalCandidats' => $totalCandidats,
            'totalCentres' => $centres->count(),
            'totalSalles' => $totalSalles,
            'totalAgentsEstimes' => $totalAgentsEstimes,
            'totalAgentsAffectes' => $totalAgentsAffectes,
            'ecartAgents' => $ecartAgents,
            'montantEstimatifTotal' => $montantEstimatifTotal,
            'montantValide' => $montantValide,
            'montantPaye' => $montantPaye,
            'detailsCentres' => $detailsCentres,
            'examens' => Examen::all(),
            'drens' => Dren::all(),
            'ciscos' => Cisco::all(),
        ]);
    }

    /**
     * Simulation instantanée avant affectation réelle.
     */
    public function simulate(Request $request)
    {
        $candidats = (int) $request->input('candidats', 0);
        $salles = (int) $request->input('salles', (int) ceil($candidats / 50));
        $typeCentre = $request->input('type_centre', 'JUMELE');
        $codeExamen = $request->input('code_examen', 'CEPE');

        $dummyCentre = new Centre([
            'id' => 0,
            'nom' => 'Centre de Simulation',
            'type_centre' => $typeCentre,
            'candidats_count' => $candidats,
            'salles_count' => $salles,
        ]);

        $estimationCentre = $this->vacationService->calculateCentreVacation($dummyCentre);

        return response()->json([
            'type_centre' => $typeCentre,
            'candidats' => $candidats,
            'salles' => $salles,
            'estimation' => $estimationCentre,
        ]);
    }

    // ... rest of controller ...
}