<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\Carrier\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CarrierPaymentAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string,mixed> */
    public function rules(): array
    {
        return [
            'account_holder_name' => ['required', 'string', 'max:160'],
            'account_holder_identification' => ['required', 'string', 'max:32'],
            'financial_institution' => ['required', 'string', 'max:120'],
            'account_type' => ['required', 'in:savings,checking,other'],
            'account_number' => ['nullable', 'digits_between:4,30'],
            'currency' => ['nullable', 'in:USD'],
            'verification_status' => ['nullable', 'in:pending,verified,blocked'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
