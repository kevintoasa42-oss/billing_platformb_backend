<?php

namespace App\Contexto\Product\Aplicacion\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CambiarEstadoProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'estado' => 'required|boolean',
        ];
    }
}
