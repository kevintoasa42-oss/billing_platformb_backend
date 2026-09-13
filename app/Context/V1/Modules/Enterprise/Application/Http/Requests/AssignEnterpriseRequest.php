<?php

namespace App\Context\V1\Modules\Enterprise\Application\Http\Requests;

class AssignEnterpriseRequest extends EnterpriseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'enterprise_id' => 'required|integer|exists:enterprises,id',
        ];
    }
}
