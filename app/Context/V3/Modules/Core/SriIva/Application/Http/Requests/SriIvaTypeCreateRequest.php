<?php

namespace App\Context\V3\Modules\Core\SriIva\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SriIvaTypeCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'percentage' => ['required', 'numeric', 'between:0,100'],
            'sri_code' => ['required', 'string', 'max:10'],
        ];
    }
}
