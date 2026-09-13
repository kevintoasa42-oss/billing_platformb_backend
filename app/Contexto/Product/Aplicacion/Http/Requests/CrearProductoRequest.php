<?php

namespace App\Contexto\Product\Aplicacion\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CrearProductoRequest extends FormRequest
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

    public static function toDTO(array $data): \App\Contexto\Product\Aplicacion\DTOs\ProductoDTO
    {
        return \App\Contexto\Product\Aplicacion\DTOs\ProductoDTO::fromArray($data);
    }
}
