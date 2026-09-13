<?php

namespace App\Context\Enterprise\Application\Http\Requests;

class CreateEnterpriseRequest extends EnterpriseFormRequest
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
     * Convierte los datos validados a un EnterpriseDTO.
     *
     * @param  array  $data
     * @return \App\Context\Enterprise\Application\DTOs\EnterpriseDTO
     */
    public static function toDTO(array $data): \App\Context\Enterprise\Application\DTOs\EnterpriseDTO
    {
        return \App\Context\Enterprise\Application\DTOs\EnterpriseDTO::fromArray($data);
    }
}
