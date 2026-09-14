<?php

namespace App\Context\V1\Modules\SriVoucherTypes\Infrastructure\Laravel\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CreateSriVoucherTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'document' => ['required', 'string', 'max:191'],
            'code' => ['required', 'string', 'max:3'],
            'sustentation_code' => ['nullable', 'string', 'max:191'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'retention' => ['sometimes', 'boolean'],
        ];
    }
}
