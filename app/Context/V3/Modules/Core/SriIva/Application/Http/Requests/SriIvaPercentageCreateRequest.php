<?php

namespace App\Context\V3\Modules\Core\SriIva\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SriIvaPercentageCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'percentage' => ['required', 'numeric', 'between:0,100'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'code' => ['nullable', 'string', 'max:20'],
        ];
    }
}
