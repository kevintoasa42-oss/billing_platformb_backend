<?php

namespace App\Context\V1\Clients\Infrastructure\Laravel\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CreateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'identification_type' => ['required', 'string', 'max:50'],
            'identification_number' => ['required', 'string', 'max:30'],
            'name' => ['required', 'string', 'max:255'], 'last_name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', 'max:50'], 'address' => ['required', 'string', 'max:500'],
            'phone' => ['required', 'string', 'max:30'], 'email' => ['required', 'email', 'max:255'],
            'type' => ['required', 'string', 'max:100'], 'plates' => ['nullable', 'string', 'max:100'],
        ];
    }
}
