<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AttributionAuditoireRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('Administrateur') === true;
    }

    public function rules(): array
    {
        return [
            'demande_auditoire_id' => ['required', 'exists:demandes_auditoire,id'],
            'auditoire_id' => ['required', 'exists:auditoires,id'],
            'statut' => ['required', 'in:Validée,Annulée'],
        ];
    }
}
