<?php

namespace App\Context\V3\Modules\Core\Carrier\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CarrierEstablishmentCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'carrier_company_id' => ['required', 'string', 'uuid'],
            'sri_code' => ['required', 'string', 'max:10'],
            'name' => ['required', 'string', 'max:255'],
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
