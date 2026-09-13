<?php

namespace App\Context\V1\Product\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'barcode' => 'nullable|string|max:100',
            'auxiliary_code' => 'nullable|string|max:100',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'boolean',
            'base_price' => 'required|numeric|min:0',
            'impuestos' => 'array',
            'impuestos.*' => 'integer|exists:pgsql.sri_iva_percentages,id',
        ];
    }

    public static function toDTO(array $data): \App\Context\V1\Product\Application\DTOs\ProductDTO
    {
        return \App\Context\V1\Product\Application\DTOs\ProductDTO::fromArray($data);
    }
}
