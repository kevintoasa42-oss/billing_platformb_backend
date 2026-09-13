<?php

namespace App\Contexto\Product\Aplicacion\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActualizarProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo_barras' => 'sometimes|nullable|string|max:100',
            'codigo_auxiliar' => 'sometimes|nullable|string|max:100',
            'nombre' => 'sometimes|string|max:255',
            'descripcion' => 'sometimes|nullable|string',
            'estado' => 'sometimes|boolean',
            'precio_base' => 'sometimes|numeric|min:0',
            'impuestos' => 'sometimes|array',
            'impuestos.*' => 'integer|exists:pgsql.sri_iva_percentages,id',
        ];
    }

    public static function toDTO(array $data): \App\Contexto\Product\Aplicacion\DTOs\ProductoDTO
    {
        return \App\Contexto\Product\Aplicacion\DTOs\ProductoDTO::fromArray($data);
    }
}
