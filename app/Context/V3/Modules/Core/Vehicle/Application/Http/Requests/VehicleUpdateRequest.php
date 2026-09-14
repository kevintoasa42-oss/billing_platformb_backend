<?php

namespace App\Context\V3\Modules\Core\Vehicle\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VehicleUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plate' => ['sometimes', 'string', 'max:20'],
            'legacy_id' => ['nullable', 'string', 'max:255'],
        ];
    }
}
