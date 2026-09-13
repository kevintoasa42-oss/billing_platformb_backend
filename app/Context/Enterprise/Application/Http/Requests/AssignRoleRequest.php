<?php

namespace App\Context\Enterprise\Application\Http\Requests;

class AssignRoleRequest extends EnterpriseFormRequest
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
