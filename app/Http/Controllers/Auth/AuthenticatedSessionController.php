<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $identifier = $request->input('email');
        $password = $request->input('password');

        // L'identifiant peut être un e-mail (tous les rôles), un matricule
        // ou un nom (étudiants). On résout d'abord l'utilisateur, puis on
        // vérifie le mot de passe, quel que soit le mode de connexion.
        $user = User::where('email', $identifier)
            ->orWhere('matricule', $identifier)
            ->orWhere('name', $identifier)
            ->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return back()
                ->withErrors(['email' => 'Identifiant ou mot de passe incorrect.'])
                ->onlyInput('email');
        }

        if (! $user->confirme) {
            return back()
                ->withErrors(['email' => 'Votre compte n\'a pas encore été validé par le Super Administrateur.'])
                ->onlyInput('email');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
