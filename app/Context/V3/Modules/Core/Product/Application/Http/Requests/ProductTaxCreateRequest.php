<?php

namespace App\Context\V3\Modules\Core\Product\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductTaxCreateRequest extends FormRequest
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
            'sri_iva_type_id' => ['required', 'integer'],
            'tax_name' => ['nullable', 'string', 'max:100'],
            'percentage' => ['nullable', 'numeric', 'min:0', 'max:100', 'decimal:0,6'],
            'sri_code' => ['nullable', 'string', 'max:25'],
            'sri_iva_percentage_id' => ['nullable', 'integer'],
        ];
    }
}
