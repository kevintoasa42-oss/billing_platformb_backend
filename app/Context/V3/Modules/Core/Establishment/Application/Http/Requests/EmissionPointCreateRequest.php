<?php

namespace App\Context\V3\Modules\Core\Establishment\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmissionPointCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'establishment_id' => ['required', 'string', 'uuid'],
            'sri_code' => ['required', 'string', 'max:10'],
            'name' => ['nullable', 'string', 'max:255'],
            'legacy_id' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
            'has_tax_validity' => ['nullable', 'boolean'],
        ];
    }
}
