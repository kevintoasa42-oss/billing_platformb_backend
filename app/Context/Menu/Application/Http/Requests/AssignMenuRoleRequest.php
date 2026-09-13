<?php

namespace App\Context\Menu\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignMenuRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rol_id' => 'required|integer|exists:roles,id',
        ];
    }
}
