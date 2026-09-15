<?php

namespace App\Context\V3\Modules\Core\Establishment\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BranchUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:150'],
            'branch_code' => ['nullable', 'string', 'max:25'],
            'address' => ['nullable', 'string', 'max:250'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'city_id' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
            'issuance_point' => ['nullable', 'array'],
            'issuance_point.name' => ['nullable', 'string', 'max:150'],
            'issuance_point.is_active' => ['nullable', 'boolean'],
            'issuance_point.is_default' => ['nullable', 'boolean'],
            'issuance_point.has_tax_validity' => ['nullable', 'boolean'],
        ];
    }
}
