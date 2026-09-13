<?php

namespace App\Context\Product\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'barcode' => 'sometimes|nullable|string|max:100',
            'auxiliary_code' => 'sometimes|nullable|string|max:100',
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
            'status' => 'sometimes|boolean',
            'base_price' => 'sometimes|numeric|min:0',
            'impuestos' => 'sometimes|array',
            'impuestos.*' => 'integer|exists:pgsql.sri_iva_percentages,id',
        ];
    }

    public static function toDTO(array $data): \App\Context\Product\Application\DTOs\ProductDTO
    {
        return \App\Context\Product\Application\DTOs\ProductDTO::fromArray($data);
    }
}
