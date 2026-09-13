<?php

namespace App\Context\V1\Partners\Infrastructure\Laravel\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdatePartnerRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'address' => ['sometimes', 'nullable', 'string', 'max:500'],
            'status' => ['sometimes', 'boolean'],
        ];
    }
}
