<?php

namespace App\Context\V1\Enterprise\Application\Http\Requests;

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
     * @return \App\Context\V1\Enterprise\Application\DTOs\LoginDTO
     */
    public static function toDTO(array $data): \App\Context\V1\Enterprise\Application\DTOs\LoginDTO
    {
        return \App\Context\V1\Enterprise\Application\DTOs\LoginDTO::fromArray($data);
    }
}
