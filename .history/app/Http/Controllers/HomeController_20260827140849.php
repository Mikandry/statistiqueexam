namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Définition de l'accès à l'espace Vacations (Administrateur uniquement)
        $canAccessVacations = $user->isAdmin(); // ou $user->role === 'admin' selon votre modèle User

        return view('home', compact('canAccessVacations'));
    }
}