<?php

namespace App\Context\V3\Modules\Core\Establishment\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EstablishmentUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sri_code' => ['sometimes', 'string', 'max:10'],
            'name' => ['sometimes', 'string', 'max:255'],
            'branch_code' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'city_id' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
            'activity_ids' => ['nullable', 'array'],
            'activity_ids.*' => ['string', 'max:100'],
        ];
    }
}
