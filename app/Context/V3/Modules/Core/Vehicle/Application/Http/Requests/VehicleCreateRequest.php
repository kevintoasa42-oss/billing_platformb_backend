<?php

namespace App\Context\V3\Modules\Core\Vehicle\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VehicleCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plate' => ['required', 'string', 'max:20'],
            'legacy_id' => ['nullable', 'string', 'max:255'],
        ];
    }
}
