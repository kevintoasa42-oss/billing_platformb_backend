<?php

namespace App\Context\V3\Modules\Core\Establishment\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IssuancePointCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'issuance_point_number' => ['nullable', 'string', 'max:3'],
            'name' => ['nullable', 'string', 'max:150'],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
            'has_tax_validity' => ['nullable', 'boolean'],
            'initial_sequences' => ['nullable', 'array'],
            'initial_sequences.*.document_type' => ['required_with:initial_sequences', 'string', 'in:invoice,credit_note,debit_note,retention,delivery_note,purchase_settlement'],
            'initial_sequences.*.last_number' => ['required_with:initial_sequences', 'integer', 'min:0', 'max:999999999'],
        ];
    }
}
