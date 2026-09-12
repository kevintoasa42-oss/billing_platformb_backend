<?php

namespace App\Contexto\Enterprise\Aplicacion\Http\Requests;

class AsignarEmpresaRequest extends EnterpriseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'enterprise_id' => 'required|integer|exists:enterprises,id',
        ];
    }
}
