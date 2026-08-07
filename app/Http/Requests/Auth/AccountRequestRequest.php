<?php

namespace App\Http\Requests\Auth;

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
            'filiere_nom' => ['nullable', 'string', 'max:255'],
            'mention_nom' => ['nullable', 'string', 'max:255'],
        ];

        $decanatRole = Role::where('nom', 'Décanat')->first();

        if ($decanatRole !== null && (int) $this->input('role_id') === $decanatRole->id) {
            $rules['domaine_id'] = ['required', 'exists:domaines,id'];
            $rules['filiere_nom'] = ['required', 'string', 'max:255'];
            $rules['mention_nom'] = ['required', 'string', 'max:255'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'role_id.required' => 'Sélectionnez le type de compte demandé.',
            'domaine_id.required' => 'Sélectionnez un Domaine.',
            'filiere_nom.required' => 'Renseignez la Filière.',
            'mention_nom.required' => 'Renseignez la Mention.',
        ];
    }

    public function isDecanatRequest(): bool
    {
        $decanatRole = Role::where('nom', 'Décanat')->first();

        return $decanatRole !== null && (int) $this->input('role_id') === $decanatRole->id;
    }
}
