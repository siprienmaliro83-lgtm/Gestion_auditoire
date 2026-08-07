<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
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
        // ou un nom (étudiants/enseignants). On résout d'abord l'utilisateur
        // par e-mail, puis par matricule, puis par nom, avant de vérifier le
        // mot de passe. La colonne `matricule` n'existe que si la migration
        // dédiée a été appliquée : on la garde donc conditionnelle pour ne
        // pas planter sur une base dont le schéma n'est pas encore à jour.
        $user = User::where('email', $identifier)->first();

        if (! $user && Schema::hasColumn('users', 'matricule')) {
            $user = User::where('matricule', $identifier)->first();
        }

        if (! $user) {
            $user = User::where('name', $identifier)->first();
        }

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
