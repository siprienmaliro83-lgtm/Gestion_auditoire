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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AccountRequestController extends Controller
{
    public function create(): View
    {
        return view('auth.account-request', [
            'roles' => Role::whereIn('nom', ['Décanat', 'Administrateur'])->orderBy('nom')->get(),
            'domaines' => Domaine::orderBy('nom')->get(),
        ]);
    }

    public function store(AccountRequestRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $userData = [
            'role_id' => $data['role_id'],
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'confirme' => false,
        ];

        if ($request->isDecanatRequest()) {
            $userData += $this->resolveDecanatScope($data);
        }

        User::create($userData);

        return redirect()
            ->route('login')
            ->with('success', 'Votre demande de compte a été soumise. Elle sera validée par le Super Administrateur.');
    }

    private function resolveDecanatScope(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $domaineId = (int) $data['domaine_id'];

            $filiere = Filiere::firstOrCreate(
                ['domaine_id' => $domaineId, 'nom' => trim($data['filiere_nom'])],
                ['code' => $this->uniqueCode('filieres', $data['filiere_nom'])],
            );

            $mention = Mention::firstOrCreate(
                ['filiere_id' => $filiere->id, 'nom' => trim($data['mention_nom'])],
                ['code' => $this->uniqueCode('mentions', $data['mention_nom'])],
            );

            return [
                'domaine_id' => $domaineId,
                'filiere_id' => $filiere->id,
                'mention_id' => $mention->id,
            ];
        });
    }

    private function uniqueCode(string $table, string $nom): string
    {
        $base = strtoupper(Str::slug($nom, '_'));
        $base = $base === '' ? 'AUTO' : $base;

        $code = $base;
        $i = 1;

        while (DB::table($table)->where('code', $code)->exists()) {
            $code = $base.'_'.(++$i);
        }

        return $code;
    }
}
