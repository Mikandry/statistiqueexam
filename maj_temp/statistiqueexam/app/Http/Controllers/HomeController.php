<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Si un utilisateur logistique accède à /home, on peut le rediriger directement
        if ($user->isLogistique()) {
            return redirect()->route('repartition.logistique.bepc-copies');
        }

        // Vérification des accès aux différentes sections
        $canAccessVacations = $user->isAdmin(); // Seuls les admins voient les vacations 2026

        return view('home', compact('canAccessVacations'));
    }
}