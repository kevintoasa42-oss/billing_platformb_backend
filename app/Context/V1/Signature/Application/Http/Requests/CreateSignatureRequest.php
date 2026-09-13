<?php

namespace App\Context\V1\Signature\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateSignatureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'carrier_id' => 'required|integer|exists:tenant.carriers,id',
            'file' => 'required|file|extensions:p12,pfx|max:10240',
            'password' => 'required|string|max:255',
            'expires_at' => 'required|date',
            'environment' => 'required|string|in:produccion,pruebas',
            'emission_type' => 'nullable|boolean',
            'status' => 'nullable|boolean',
        ];
    }

    protected function prepareForValidation(): void
    {
        foreach (['emission_type', 'status'] as $field) {
            if ($this->has($field)) {
                $val = $this->input($field);
                if ($val === 'true' || $val === '1') $this->merge([$field => true]);
                elseif ($val === 'false' || $val === '0') $this->merge([$field => false]);
            }
        }
    }

    public static function toDTO(array $data): \App\Context\V1\Signature\Application\DTOs\SignatureDTO
    {
        return \App\Context\V1\Signature\Application\DTOs\SignatureDTO::fromArray($data);
    }
}
