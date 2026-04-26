<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, (bool) $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Identifiants invalides.']);
        }

        $request->session()->regenerate();
        AuditLog::record($request, 'login', ['email' => $credentials['email']]);

        $defaultRoute = Auth::user()?->isLogistique()
            ? route('repartition.logistique.bepc-copies')
            : route('bepc.repartition.create');

        return redirect()->intended($defaultRoute);
    }

    public function showRegisterForm(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => trim((string) $validated['name']),
            'email' => strtolower(trim((string) $validated['email'])),
            'password' => Hash::make((string) $validated['password']),
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        AuditLog::record($request, 'register', ['email' => $user->email]);

        return redirect()->intended(route('bepc.repartition.create'));
    }

    public function logout(Request $request): RedirectResponse
    {
        AuditLog::record($request, 'logout');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
