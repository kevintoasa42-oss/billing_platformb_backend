<?php

namespace App\Context\V1\EmissionPoints\Infrastructure\Laravel\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class NextSequentialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'branch_office_id' => ['required', 'integer', 'exists:tenant.branch_offices,id'],
            'emission_point_id' => ['nullable', 'integer', 'required_without:emission_point'],
            'emission_point' => ['nullable', 'string', 'max:20', 'required_without:emission_point_id'],
            'carrier_id' => ['nullable', 'integer', 'exists:tenant.carriers,id'],
            'document_code' => ['required', 'string', 'max:3'],
        ];
    }
}
