<?php

namespace App\Context\V1\Enterprise\Application\Http\Requests;

class AssignRoleRequest extends EnterpriseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role_id' => 'required|integer|exists:roles,id',
        ];
    }
}
