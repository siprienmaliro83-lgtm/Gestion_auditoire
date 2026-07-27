<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role_id' => ['required', 'exists:roles,id'],
            'domaine_id' => [
                'nullable',
                'exists:domaines,id',
                Rule::requiredIf(fn (): bool => $this->selectedRoleName() === 'Décanat'),
            ],
            'promotion_id' => [
                'nullable',
                'exists:promotions,id',
                Rule::requiredIf(fn (): bool => $this->selectedRoleName() === 'Étudiant'),
            ],
        ];
    }

    private function selectedRoleName(): ?string
    {
        $roleId = $this->input('role_id');

        if (! $roleId) {
            return null;
        }

        return \App\Models\Role::whereKey($roleId)->value('nom');
    }
}
