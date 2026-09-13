<?php

namespace App\Context\V1\Carrier\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCarrierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ruc' => 'sometimes|required|string|max:13',
            'placa' => 'sometimes|required|string|max:20',
            'name' => 'sometimes|required|string|max:255',
            'tradename' => 'sometimes|nullable|string|max:255',
            'matrix_address' => 'sometimes|nullable|string|max:500',
            'special_taxpayer' => 'sometimes|nullable|string|max:50',
            'accounting_required' => 'sometimes|boolean',
            'status' => 'sometimes|boolean',
        ];
    }

    public static function toDTO(array $data): \App\Context\V1\Carrier\Application\DTOs\CarrierDTO
    {
        return \App\Context\V1\Carrier\Application\DTOs\CarrierDTO::fromArray($data);
    }
}
