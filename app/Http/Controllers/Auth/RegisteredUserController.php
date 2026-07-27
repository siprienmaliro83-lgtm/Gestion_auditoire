<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Domaine;
use App\Models\Enseignant;
use App\Models\Promotion;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register', [
            'roles' => Role::orderBy('nom')->get(),
            'domaines' => Domaine::orderBy('nom')->get(),
            'promotions' => Promotion::with('mention.filiere.domaine')->orderBy('nom')->get(),
        ]);
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $role = Role::findOrFail($data['role_id']);

        if ($role->nom !== 'Décanat') {
            $data['domaine_id'] = null;
        }

        if ($role->nom !== 'Étudiant') {
            $data['promotion_id'] = null;
        }

        $user = User::create($data);

        if ($role->nom === 'Enseignant') {
            $count = Enseignant::count() + 1;
            Enseignant::create([
                'user_id' => $user->id,
                'matricule' => sprintf('ENS-%04d', $count),
                'nom' => $user->name,
                'prenom' => null,
                'email' => $user->email,
                'telephone' => null,
                'grade' => null,
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
