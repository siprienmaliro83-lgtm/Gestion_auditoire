<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ComptesController extends Controller
{
    public function index(): View
    {
        $pending = User::with('role', 'domaine', 'filiere', 'mention')
            ->where('confirme', false)
            ->whereHas('role', fn ($q) => $q->whereIn('nom', ['Décanat', 'Administrateur']))
            ->latest()
            ->get();

        $users = User::with('role', 'domaine', 'filiere', 'mention')
            ->latest()
            ->paginate(20);

        return view('admin.comptes.index', [
            'pending' => $pending,
            'users' => $users,
        ]);
    }

    public function approuver(int $id): RedirectResponse
    {
        $user = User::findOrFail($id);
        $this->guardSelf($user);

        $user->confirme = true;
        $user->save();

        return back()->with('success', 'Compte approuvé : « '.$user->name.' » peut maintenant se connecter.');
    }

    public function refuser(int $id): RedirectResponse
    {
        $user = User::findOrFail($id);
        $this->guardSelf($user);

        $name = $user->name;
        $user->delete();

        return back()->with('success', 'Demande de compte refusée pour « '.$name.' ».');
    }

    public function activer(int $id): RedirectResponse
    {
        $user = User::findOrFail($id);
        $this->guardSelf($user);

        $user->confirme = true;
        $user->save();

        return back()->with('success', 'Compte activé : « '.$user->name.' ».');
    }

    public function desactiver(int $id): RedirectResponse
    {
        $user = User::findOrFail($id);
        $this->guardSelf($user);

        $user->confirme = false;
        $user->save();

        return back()->with('success', 'Compte désactivé : « '.$user->name.' ».');
    }

    private function guardSelf(User $user): void
    {
        abort_if(Auth::id() === $user->id, 403, 'Vous ne pouvez pas modifier le statut de votre propre compte.');
    }
}
