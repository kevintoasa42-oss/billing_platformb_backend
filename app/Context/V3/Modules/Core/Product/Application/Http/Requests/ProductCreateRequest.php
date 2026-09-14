<?php

namespace App\Context\V3\Modules\Core\Product\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductCreateRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:200'],
            'reference_price' => ['required', 'numeric', 'min:0', 'decimal:0,6'],
            'activity_id' => ['required', 'string', 'max:100'],
            'barcode' => ['nullable', 'string', 'max:100'],
            'auxiliary_code' => ['nullable', 'string', 'max:50'],
            'other_code' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'type' => ['nullable', 'string', 'in:product,service'],
            'is_active' => ['nullable', 'boolean'],
            'sri_iva_type_ids' => ['nullable', 'array'],
            'sri_iva_type_ids.*' => ['integer'],
        ];
    }
}
