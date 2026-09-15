<?php

namespace App\Context\V3\Modules\Core\Establishment\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IssuancePointUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:150'],
            'issuance_point_number' => ['nullable', 'string', 'max:3'],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
            'has_tax_validity' => ['nullable', 'boolean'],
            'sequences' => ['nullable', 'array'],
            'sequences.*.document_type' => ['required_with:sequences', 'string', 'in:invoice,credit_note,debit_note,retention,delivery_note,purchase_settlement'],
            'sequences.*.last_number' => ['required_with:sequences', 'integer', 'min:0', 'max:999999999'],
        ];
    }
}
