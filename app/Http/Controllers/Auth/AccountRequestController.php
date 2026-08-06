<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AccountRequestRequest;
use App\Models\Domaine;
use App\Models\Filiere;
use App\Models\Mention;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AccountRequestController extends Controller
{
    public function create(): View
    {
        $domaineId = old('domaine_id') ? (int) old('domaine_id') : null;
        $filiereId = old('filiere_id') ? (int) old('filiere_id') : null;

        return view('auth.account-request', [
            'roles' => Role::whereIn('nom', ['Décanat', 'Administrateur'])->orderBy('nom')->get(),
            'domaines' => Domaine::orderBy('nom')->get(),
            'filieres' => $domaineId ? Filiere::where('domaine_id', $domaineId)->orderBy('nom')->get() : collect(),
            'mentions' => $filiereId ? Mention::where('filiere_id', $filiereId)->orderBy('nom')->get() : collect(),
        ]);
    }

    public function store(AccountRequestRequest $request): RedirectResponse
    {
        $data = $request->validated();

        User::create([
            'role_id' => $data['role_id'],
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'confirme' => false,
            'domaine_id' => $request->isDecanatRequest() ? $data['domaine_id'] : null,
            'filiere_id' => $request->isDecanatRequest() ? $data['filiere_id'] : null,
            'mention_id' => $request->isDecanatRequest() ? $data['mention_id'] : null,
        ]);

        return redirect()
            ->route('login')
            ->with('success', 'Votre demande de compte a été soumise. Elle sera validée par le Super Administrateur.');
    }
}
