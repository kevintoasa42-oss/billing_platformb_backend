<?php

namespace App\Contexto\Enterprise\Aplicacion\Http\Requests;

class CrearUsuarioRequest extends EnterpriseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:usuarios,email',
            'password' => 'required|string|min:8',
        ];
    }

    /**
     * Convierte los datos validados a un UsuarioDTO.
     *
     * @param  array  $data
     * @return \App\Contexto\Enterprise\Aplicacion\DTOs\UsuarioDTO
     */
    public static function toDTO(array $data): \App\Contexto\Enterprise\Aplicacion\DTOs\UsuarioDTO
    {
        return \App\Contexto\Enterprise\Aplicacion\DTOs\UsuarioDTO::fromArray($data);
    }
}
