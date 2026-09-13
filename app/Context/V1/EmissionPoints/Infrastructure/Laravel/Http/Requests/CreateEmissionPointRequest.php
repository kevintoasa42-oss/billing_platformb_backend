<?php

namespace App\Context\V1\EmissionPoints\Infrastructure\Laravel\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CreateEmissionPointRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'branch_office_id' => ['required', 'integer', 'exists:tenant.branch_offices,id'],
            'name' => ['required', 'string', 'max:255'], 'emission_point' => ['required', 'string', 'max:20'],
            'status' => ['sometimes', 'boolean'], 'default' => ['sometimes', 'boolean'],
        ];
    }
}
