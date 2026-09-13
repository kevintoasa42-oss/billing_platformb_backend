<?php

namespace App\Context\Product\Application\Http\Requests;

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
            'codigo_barras' => 'nullable|string|max:100',
            'codigo_auxiliar' => 'nullable|string|max:100',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'estado' => 'boolean',
            'precio_base' => 'required|numeric|min:0',
            'impuestos' => 'array',
            'impuestos.*' => 'integer|exists:pgsql.sri_iva_percentages,id',
        ];
    }

    public static function toDTO(array $data): \App\Context\Product\Application\DTOs\ProductDTO
    {
        return \App\Context\Product\Application\DTOs\ProductDTO::fromArray($data);
    }
}
