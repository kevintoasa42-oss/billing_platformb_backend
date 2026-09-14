<?php

namespace App\Context\V3\Modules\Core\SriIva\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SriIvaTypeUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'percentage' => ['sometimes', 'required', 'numeric', 'between:0,100'],
            'sri_code' => ['sometimes', 'required', 'string', 'max:10'],
        ];
    }
}
