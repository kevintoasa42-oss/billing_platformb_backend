<?php

namespace App\Context\V3\Modules\Core\Settings\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentMethodUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'alias' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
