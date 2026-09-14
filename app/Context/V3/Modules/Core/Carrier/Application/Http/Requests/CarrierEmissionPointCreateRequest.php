<?php

namespace App\Context\V3\Modules\Core\Carrier\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CarrierEmissionPointCreateRequest extends FormRequest
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
            'establishment_id' => ['required', 'string', 'uuid'],
            'sri_code' => ['required', 'string', 'max:10'],
            'name' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
