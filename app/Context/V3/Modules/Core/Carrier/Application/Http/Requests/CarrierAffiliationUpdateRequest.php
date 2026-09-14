<?php

namespace App\Context\V3\Modules\Core\Carrier\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CarrierAffiliationUpdateRequest extends FormRequest
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
            'validity' => ['nullable', 'string'],
            'vehicle_assignments' => ['nullable', 'array'],
            'vehicle_assignments.*.vehicle_id' => ['required', 'string', 'uuid'],
            'vehicle_assignments.*.validity' => ['nullable', 'string'],
        ];
    }
}
