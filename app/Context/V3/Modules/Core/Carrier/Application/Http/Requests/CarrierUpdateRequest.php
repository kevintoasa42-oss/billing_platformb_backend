<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\Carrier\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CarrierUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string,mixed> */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'legal_name' => ['sometimes', 'string', 'max:255'],
            'trade_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'is_active' => ['sometimes', 'boolean'],
            'activity_id' => ['nullable', 'string', 'max:100'],
            'activity_valid_from' => ['nullable', 'date_format:Y-m-d'],
            'establishment_code' => ['nullable', 'digits:3'],
            'plates' => ['nullable', 'array', 'max:100'],
            'plates.*' => ['string', 'max:20'],
            'plate' => ['nullable', 'string', 'max:20'],
        ];
    }
}
