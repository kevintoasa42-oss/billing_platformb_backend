<?php

namespace App\Context\V1\Enterprise\Application\Http\Requests;

class CreateEnterpriseRequest extends EnterpriseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'ruc' => 'required|string|max:13|unique:enterprises,ruc',
            'tradename' => 'required|string|max:255',
            'matrix_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'corporate_email' => 'required|email|max:255',
        ];
    }

    /**
     * Convierte los datos validados a un EnterpriseDTO.
     *
     * @param  array  $data
     * @return \App\Context\V1\Enterprise\Application\DTOs\EnterpriseDTO
     */
    public static function toDTO(array $data): \App\Context\V1\Enterprise\Application\DTOs\EnterpriseDTO
    {
        return \App\Context\V1\Enterprise\Application\DTOs\EnterpriseDTO::fromArray($data);
    }
}
