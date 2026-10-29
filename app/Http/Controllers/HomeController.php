<?php

namespace App\Http\Controllers;

use App\Models\CentreEcrit;
use App\Models\Dren;
use App\Models\HrEvent;
use App\Models\InventoryMaterial;
use App\Models\RepartitionSalle;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Page d'accueil selon le rôle. Les cartes affichées reprennent exactement les droits des routes :
 * la page n'accorde aucun accès, elle ne fait que guider (les routes restent protégées côté serveur
 * par leurs propres middlewares et policies).
 */
class HomeController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->isLogistique()) {
            return redirect()->route('repartition.logistique.bepc-copies');
        }

        $cards = [];
        $indicators = [];

        $canStats = $user->isAdmin() || $user->isStats() || $user->isExams() || $user->role === User::ROLE_USER;
        if ($canStats) {
            $cards[] = ['title' => 'Statistiques et répartition', 'text' => 'Saisie des salles et candidats, répartitions, rapports.', 'route' => 'bepc.repartition.create', 'tone' => 'blue'];
        }

        // Indicateurs : uniquement pour ceux qui peuvent déjà ouvrir les pages correspondantes.
        if ($user->isAdmin() || $user->isStats() || $user->isExams()) {
            $indicators[] = ['label' => 'DREN', 'value' => Dren::query()->count()];
            $indicators[] = ['label' => "Centres d'écrit", 'value' => CentreEcrit::query()->count()];
            $indicators[] = ['label' => 'Salles saisies', 'value' => RepartitionSalle::query()->count()];
        }

        if ($user->canAccessRh()) {
            $cards[] = ['title' => 'Ressources humaines', 'text' => 'Personnel, disponibilités, validations en attente.', 'route' => 'hr.dashboard', 'tone' => 'green'];
            $indicators[] = ['label' => 'Validations RH en attente', 'value' => HrEvent::query()->where('status', 'demande')->count()];
        } else {
            $cards[] = ['title' => 'Mon dossier personnel', 'text' => 'Mes informations, congés, absences et demandes.', 'route' => 'hr.dashboard', 'tone' => 'green'];
        }

        if ($user->isAdmin()) {
            $cards[] = ['title' => 'Vacation 2026', 'text' => 'Personnes mobilisées et montants par province, DREN, CISCO et centre.', 'route' => 'vacation2026.besoins.dashboard', 'tone' => 'violet'];
            $cards[] = ['title' => 'Bilan des activités du personnel', 'text' => 'Formations, absences, ateliers et congés par agent.', 'route' => 'hr.activity.index', 'tone' => 'green'];
            $cards[] = ['title' => 'Comptabilité des matières', 'text' => 'Stocks, mouvements et rapprochement.', 'route' => 'inventory.index', 'tone' => 'amber'];
            $cards[] = ['title' => 'Références', 'text' => 'Provinces, DREN, CISCO, centres et dispatching.', 'route' => 'admin.references.index', 'tone' => 'slate'];
            $indicators[] = ['label' => 'Matériels en alerte de stock', 'value' => InventoryMaterial::query()->get()->filter->needsSupply()->count()];
        }

        return view('home', [
            'cards' => $cards,
            'indicators' => $indicators,
            'roleLabel' => match ($user->role) {
                User::ROLE_ADMIN => 'Administrateur national',
                User::ROLE_RH => 'Ressources humaines',
                User::ROLE_STATS => 'Statistiques',
                User::ROLE_EXAMENS => 'Examens',
                default => 'Utilisateur',
            },
        ]);
    }
}
