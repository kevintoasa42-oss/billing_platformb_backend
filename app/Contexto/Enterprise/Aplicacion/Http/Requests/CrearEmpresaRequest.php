<?php

namespace App\Contexto\Enterprise\Aplicacion\Http\Requests;

class CrearEmpresaRequest extends EnterpriseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'ruc' => 'required|string|max:13|unique:enterprises,ruc',
            'tradename' => 'required|string|max:255',
            'matrixname' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'correo_corporativo' => 'required|email|max:255',
        ];
    }

    /**
     * Convierte los datos validados a un EmpresaDTO.
     *
     * @param  array  $data
     * @return \App\Contexto\Enterprise\Aplicacion\DTOs\EmpresaDTO
     */
    public static function toDTO(array $data): \App\Contexto\Enterprise\Aplicacion\DTOs\EmpresaDTO
    {
        return \App\Contexto\Enterprise\Aplicacion\DTOs\EmpresaDTO::fromArray($data);
    }
}
