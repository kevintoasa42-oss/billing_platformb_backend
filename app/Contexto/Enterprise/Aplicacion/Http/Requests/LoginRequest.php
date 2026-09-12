<?php

namespace App\Contexto\Enterprise\Aplicacion\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string',
            'enterprise_id' => 'required|integer',
        ];
    }

    /**
     * Convierte los datos validados a un LoginDTO.
     *
     * @param  array  $data
     * @return \App\Contexto\Enterprise\Aplicacion\DTOs\LoginDTO
     */
    public static function toDTO(array $data): \App\Contexto\Enterprise\Aplicacion\DTOs\LoginDTO
    {
        return \App\Contexto\Enterprise\Aplicacion\DTOs\LoginDTO::fromArray($data);
    }
}
