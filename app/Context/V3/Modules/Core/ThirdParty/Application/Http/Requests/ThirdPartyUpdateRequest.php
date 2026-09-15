<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ThirdPartyUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('identification')) {
            return;
        }

        $identification = strtoupper((string) preg_replace('/\s+/', '', trim((string) $this->input('identification', ''))));
        $type = strtoupper(trim((string) $this->input('identification_type', '')));
        $type = match ($type) {
            'RUC' => '04',
            'CED', 'CI', 'CÉDULA' => '05',
            'PAS', 'PASSPORT' => '06',
            'CF', 'CONSUMIDOR FINAL' => '07',
            default => $type !== '' ? $type : (strlen($identification) === 13 ? '04' : '05'),
        };

        $this->merge(['identification' => $identification, 'identification_type' => $type]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'identification' => ['sometimes', 'nullable', 'string', 'max:32', 'regex:/^[A-Z0-9._-]+$/'],
            'must_invoice' => ['nullable', 'boolean'],
            'identification_type' => ['sometimes', 'nullable', 'string', 'in:04,05,06,07'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'customer_type_id' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
            'role' => ['sometimes', 'string', 'in:customer,carrier'],
            'roles' => ['sometimes', 'array', 'min:1'],
            'roles.*' => ['string', 'in:customer,carrier,supplier,member', 'distinct'],
            'custom_fields' => ['sometimes', 'array', 'max:100'],
        ];
    }
}
