<?php

namespace App\Context\V3\Modules\Core\Product\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:200'],
            'reference_price' => ['sometimes', 'numeric', 'min:0', 'decimal:0,6'],
            'activity_id' => ['sometimes', 'string', 'max:100'],
            'barcode' => ['nullable', 'string', 'max:100'],
            'auxiliary_code' => ['nullable', 'string', 'max:50'],
            'other_code' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'type' => ['sometimes', 'string', 'in:product,service'],
            'is_active' => ['sometimes', 'boolean'],
            'sri_iva_type_ids' => ['nullable', 'array'],
            'sri_iva_type_ids.*' => ['integer'],
        ];
    }
}
