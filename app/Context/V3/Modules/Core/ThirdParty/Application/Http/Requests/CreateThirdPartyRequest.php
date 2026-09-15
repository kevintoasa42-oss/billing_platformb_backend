<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Application\Http\Requests;

use App\Context\V3\Modules\Core\ThirdParty\Domain\ValueObjects\CanonicalIdentification;
use Illuminate\Foundation\Http\FormRequest;

final class CreateThirdPartyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $identification = CanonicalIdentification::normalize($this->input('identification'));
        $type = strtoupper(trim((string) $this->input('identification_type', '')));
        $type = match ($type) {
            'RUC' => '04',
            'CED', 'CI', 'CÉDULA', 'CEDULA' => '05',
            'PAS', 'PASSPORT' => '06',
            'CF', 'CONSUMIDOR FINAL' => '07',
            default => $type !== '' ? $type : (strlen($identification) === 13 ? '04' : '05'),
        };

        $this->merge([
            'name' => trim((string) $this->input('name', '')),
            'identification' => $identification,
            'identification_type' => $type,
        ]);
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'identification' => ['required', 'string', 'max:32', 'regex:/^[A-Z0-9._-]+$/'],
            'identification_type' => ['required', 'string', 'in:04,05,06,07'],
            'must_invoice' => ['nullable', 'boolean'],
            'person_type' => ['nullable', 'string', 'in:natural,juridical'],
            'legacy_id' => ['nullable', 'integer'],
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
