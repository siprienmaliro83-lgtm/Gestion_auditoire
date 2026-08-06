<?php

namespace App\Http\Requests\Auth;

use App\Models\Filiere;
use App\Models\Mention;
use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AccountRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role_id' => [
                'required',
                function (string $attribute, $value, $fail): void {
                    $allowed = Role::whereIn('nom', ['Décanat', 'Administrateur'])->pluck('id');
                    if (! $allowed->contains((int) $value)) {
                        $fail('Ce rôle ne peut pas être demandé. Seuls les comptes Décanat ou Administrateur sont concernés.');
                    }
                },
            ],
            'domaine_id' => ['nullable', 'exists:domaines,id'],
            'filiere_id' => ['nullable', 'exists:filieres,id'],
            'mention_id' => ['nullable', 'exists:mentions,id'],
        ];

        $decanatRole = Role::where('nom', 'Décanat')->first();

        if ($decanatRole !== null && (int) $this->input('role_id') === $decanatRole->id) {
            $domaineId = (int) $this->input('domaine_id');
            $filiereId = (int) $this->input('filiere_id');

            $rules['domaine_id'] = ['required', 'exists:domaines,id'];

            $rules['filiere_id'] = [
                'required',
                'exists:filieres,id',
                function (string $attribute, $value, $fail) use ($domaineId): void {
                    if (! Filiere::whereKey($value)->where('domaine_id', $domaineId)->exists()) {
                        $fail('La filière choisie n\'appartient pas au domaine sélectionné.');
                    }
                },
            ];

            $rules['mention_id'] = [
                'required',
                'exists:mentions,id',
                function (string $attribute, $value, $fail) use ($filiereId): void {
                    if (! Mention::whereKey($value)->where('filiere_id', $filiereId)->exists()) {
                        $fail('La mention choisie n\'appartient pas à la filière sélectionnée.');
                    }
                },
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'role_id.required' => 'Sélectionnez le type de compte demandé.',
            'domaine_id.required' => 'Sélectionnez un Domaine.',
            'filiere_id.required' => 'Sélectionnez une Filière.',
            'mention_id.required' => 'Sélectionnez une Mention.',
        ];
    }

    public function isDecanatRequest(): bool
    {
        $decanatRole = Role::where('nom', 'Décanat')->first();

        return $decanatRole !== null && (int) $this->input('role_id') === $decanatRole->id;
    }
}
