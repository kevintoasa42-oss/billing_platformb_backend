<?php

namespace App\Context\V1\Partners\Infrastructure\Laravel\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CreatePartnerRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
            'status' => ['sometimes', 'boolean'],
        ];
    }
}
