<?php

namespace App\Contexto\Enterprise\Aplicacion\Http\Requests;

class AsignarRolRequest extends EnterpriseFormRequest
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
