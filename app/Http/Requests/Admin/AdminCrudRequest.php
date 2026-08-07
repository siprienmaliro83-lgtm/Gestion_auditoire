<?php

namespace App\Http\Requests\Admin;

use App\Http\Controllers\Admin\AdminCrudController;
use Illuminate\Foundation\Http\FormRequest;

class AdminCrudRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(['Administrateur', 'Super Administrateur']) === true;
    }

    public function rules(): array
    {
        $resource = (string) $this->route('resource');
        $id = $this->route('id');

        return AdminCrudController::rulesFor($resource, $id ? (int) $id : null);
    }
}
