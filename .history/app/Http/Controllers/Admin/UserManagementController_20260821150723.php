<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HrAgent;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    private const AVAILABLE_ROLES = [
        User::ROLE_ADMIN,
        User::ROLE_USER,
        User::ROLE_LOGISTIQUE,
    ];

    public function index(Request $request): View
    {
        $users = User::query()
            ->with('hrAgent')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:'.implode(',', self::AVAILABLE_ROLES)],
        ]);

        $email = strtolower(trim((string) $validated['email']));

        DB::transaction(function () use ($validated, $email) {
            $agent = HrAgent::resolveForUser(
                trim((string) $validated['name']),
                $email
            );

            User::create([
                'name' => trim((string) $validated['name']),
                'email' => $email,
                'password' => Hash::make((string) $validated['password']),
                'role' => $validated['role'],
                'hr_agent_id' => $agent?->id,
            ]);
        });

        return back()->with('status', 'Utilisateur ajouté.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', 'in:'.implode(',', self::AVAILABLE_ROLES)],
        ]);

        $data = [
            'name' => trim((string) $validated['name']),
            'email' => strtolower(trim((string) $validated['email'])),
            'role' => $validated['role'],
        ];

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make((string) $validated['password']);
        }

        $email = $data['email'];

        DB::transaction(function () use ($user, $data, $email) {
            if ($user->hr_agent_id) {
                HrAgent::query()
                    ->whereKey($user->hr_agent_id)
                    ->whereRaw('LOWER(email) <> ?', [$email])
                    ->update(['email' => null]);
            }

            $agent = HrAgent::query()
                ->whereRaw('LOWER(email) = ?', [$email])
                ->first();

            if ($agent?->user && $agent->user->id !== $user->id) {
                $agent = null;
            }

            $agent ??= HrAgent::resolveForUser($data['name'], $email);

            $user->update($data + ['hr_agent_id' => $agent?->id]);
        });

        return back()->with('status', 'Utilisateur modifié.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ((int) $request->user()->id === (int) $user->id) {
            return back()->withErrors(['user' => 'Vous ne pouvez pas supprimer votre propre compte.']);
        }

        $user->delete();

        return back()->with('status', 'Utilisateur supprimé.');
    }

}
