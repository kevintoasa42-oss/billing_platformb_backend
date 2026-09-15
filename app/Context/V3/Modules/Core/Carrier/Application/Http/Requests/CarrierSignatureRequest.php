<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\Carrier\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CarrierSignatureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string,mixed> */
    public function rules(): array
    {
        return [
            'status' => ['required', 'in:missing,active,expired,revoked,invalid'],
            'certificate_fingerprint' => ['nullable', 'string', 'max:128'],
            'certificate_serial' => ['nullable', 'string', 'max:255'],
            'certificate_subject' => ['nullable', 'string', 'max:255'],
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date'],
            'key_reference' => ['nullable', 'string', 'max:255'],
            'key_version' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
