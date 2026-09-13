<?php

namespace App\Context\Enterprise\Application\Http\Requests;

class CreateUserRequest extends EnterpriseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
        ];
    }

    /**
     * Convierte los datos validados a un UserDTO.
     *
     * @param  array  $data
     * @return \App\Context\Enterprise\Application\DTOs\UserDTO
     */
    public static function toDTO(array $data): \App\Context\Enterprise\Application\DTOs\UserDTO
    {
        return \App\Context\Enterprise\Application\DTOs\UserDTO::fromArray($data);
    }
}
