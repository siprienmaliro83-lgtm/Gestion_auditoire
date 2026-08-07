<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RejetDemandeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(['Administrateur', 'Super Administrateur']) === true;
    }

    public function rules(): array
    {
        return [
            'motif_refus' => ['required', 'string', 'min:3', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'motif_refus.required' => 'Le motif de rejet est obligatoire.',
            'motif_refus.min' => 'Le motif de rejet doit contenir au moins 3 caractères.',
        ];
    }
}
