<?php

namespace App\Http\Requests\Decanat;

use App\Http\Controllers\Decanat\DecanatCrudController;
use Illuminate\Foundation\Http\FormRequest;

class DecanatCrudRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $resource = $this->route('resource');
        $id = $this->route('id');

        return DecanatCrudController::rulesFor($resource, $id);
    }
}
