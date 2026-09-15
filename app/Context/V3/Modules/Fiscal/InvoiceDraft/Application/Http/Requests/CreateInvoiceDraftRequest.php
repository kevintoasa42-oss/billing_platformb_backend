<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\InvoiceDraft\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class CreateInvoiceDraftRequest extends FormRequest
{
    private const MAX_PAYLOAD_BYTES = 262_144;

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'payload' => ['required', 'array', 'max:20'],
            'payload.items' => ['sometimes', 'array', 'max:100'],
            'payload.additionalInfo' => ['sometimes', 'array', 'max:20'],
            'revision' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /** @return array<int, \Closure(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $encoded = json_encode($this->input('payload', []));
            if ($encoded === false || strlen($encoded) > self::MAX_PAYLOAD_BYTES) {
                $validator->errors()->add('payload', 'El borrador supera el tamaño máximo permitido.');
            }
        }];
    }
}
