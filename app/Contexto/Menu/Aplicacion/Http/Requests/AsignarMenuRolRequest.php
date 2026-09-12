<?php

namespace App\Contexto\Menu\Aplicacion\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AsignarMenuRolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rol_id' => 'required|integer|exists:roles,id',
        ];
    }
}
