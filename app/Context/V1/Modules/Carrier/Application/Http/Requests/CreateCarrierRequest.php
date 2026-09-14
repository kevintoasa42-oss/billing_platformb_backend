<?php

namespace App\Context\V1\Modules\Carrier\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateCarrierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ruc' => 'required|string|max:13',
            'plate' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'tradename' => 'nullable|string|max:255',
            'matrix_address' => 'nullable|string|max:500',
            'special_taxpayer' => 'nullable|string|max:50',
            'accounting_required' => 'boolean',
            'status' => 'boolean',
        ];
    }

    public static function toDTO(array $data): \App\Context\V1\Modules\Carrier\Application\DTOs\CarrierDTO
    {
        return \App\Context\V1\Modules\Carrier\Application\DTOs\CarrierDTO::fromArray($data);
    }
}
