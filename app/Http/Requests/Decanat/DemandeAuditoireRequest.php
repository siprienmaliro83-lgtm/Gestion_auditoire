<?php

namespace App\Http\Requests\Decanat;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DemandeAuditoireRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('Décanat') === true;
    }

    public function rules(): array
    {
        return [
            'ec_id' => ['required', 'exists:ecs,id'],
            'enseignant_id' => ['required', 'exists:enseignants,id'],
            'promotions_concernees' => ['required', 'array', 'min:1'],
            'promotions_concernees.*' => ['integer', 'exists:promotions,id'],
            'date_debut' => ['required', 'date'],
            'date_fin' => ['required', 'date', 'after_or_equal:date_debut'],
            'heure_debut' => ['required', 'date_format:H:i'],
            'heure_fin' => ['required', 'date_format:H:i', 'after:heure_debut'],
            'effectif_total' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'promotions_concernees.min' => 'Sélectionnez au moins une promotion concernée.',
            'heure_fin.after' => 'L\'heure de fin doit être postérieure à l\'heure de début.',
        ];
    }
}
