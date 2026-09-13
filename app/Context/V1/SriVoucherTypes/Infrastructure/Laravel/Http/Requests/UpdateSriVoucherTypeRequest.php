<?php

namespace App\Context\V1\SriVoucherTypes\Infrastructure\Laravel\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateSriVoucherTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'document' => ['sometimes', 'string', 'max:191'],
            'code' => ['sometimes', 'string', 'max:3'],
            'sustentation_code' => ['sometimes', 'nullable', 'string', 'max:191'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:start_date'],
            'retention' => ['sometimes', 'boolean'],
        ];
    }
}
