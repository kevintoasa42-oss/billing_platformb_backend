<?php

namespace App\Context\V1\Clients\Infrastructure\Laravel\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'identification_type' => ['sometimes', 'string', 'max:50'],
            'identification_number' => ['sometimes', 'string', 'max:30'],
            'name' => ['sometimes', 'string', 'max:255'], 'last_name' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'max:50'], 'address' => ['sometimes', 'string', 'max:500'],
            'phone' => ['sometimes', 'string', 'max:30'], 'email' => ['sometimes', 'email', 'max:255'],
            'type' => ['sometimes', 'string', 'max:100'], 'plates' => ['sometimes', 'nullable', 'string', 'max:100'],
        ];
    }
}
