<?php

namespace App\Context\V1\Modules\EmissionPoints\Infrastructure\Laravel\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateEmissionPointRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'branch_office_id' => ['sometimes', 'integer', 'exists:tenant.branch_offices,id'],
            'name' => ['sometimes', 'string', 'max:255'], 'emission_point' => ['sometimes', 'string', 'max:20'],
            'status' => ['sometimes', 'boolean'], 'default' => ['sometimes', 'boolean'],
        ];
    }
}
