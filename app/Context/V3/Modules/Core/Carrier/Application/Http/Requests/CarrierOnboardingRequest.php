<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\Carrier\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CarrierOnboardingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];
        if ($this->has('issuer_mode')) {
            $normalized['issuer_mode'] = strtolower(trim((string) $this->input('issuer_mode')));
        }
        if ($this->has('person_type')) {
            $normalized['person_type'] = $this->input('person_type') === null
                ? null
                : strtolower(trim((string) $this->input('person_type')));
        }
        if ($normalized !== []) {
            $this->merge($normalized);
        }
    }

    /** @return array<string,mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['required', 'string', 'max:255'],
            'trade_name' => ['nullable', 'string', 'max:255'],
            'person_type' => ['sometimes', 'nullable', 'string', 'in:natural,juridical'],
            'identification' => ['required', 'string', 'max:32', 'regex:/^[A-Za-z0-9._-]+$/'],
            'identification_type' => ['required', 'string', 'in:04,05,06,07,RUC,CED,PAS,CF'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'activity_id' => ['nullable', 'string', 'max:100'],
            'activity_valid_from' => ['nullable', 'date_format:Y-m-d'],
            'plates' => ['nullable', 'array', 'max:100'],
            'plates.*' => ['string', 'max:20'],
            'plate' => ['nullable', 'string', 'max:20'],
            // When omitted, the tenant's carrier_issuer_settings value is
            // authoritative. An explicit request still validates strictly.
            'issuer_mode' => ['sometimes', 'nullable', 'in:operator,partner'],
            'operator_establishment_code' => ['nullable', 'digits:3'],
            'operator_emission_point_code' => ['nullable', 'digits:3'],
            'partner_ruc' => ['required_if:issuer_mode,partner', 'digits:13'],
            'partner_establishment_code' => ['required_if:issuer_mode,partner', 'digits:3'],
            'partner_emission_point_code' => ['required_if:issuer_mode,partner', 'digits:3'],
            'partner_next_sequential' => ['required_if:issuer_mode,partner', 'integer', 'between:1,999999999'],
            'partner_key_reference' => ['required_if:issuer_mode,partner', 'string', 'max:255'],
            'payment_account' => ['nullable', 'array'],
        ];
    }
}
