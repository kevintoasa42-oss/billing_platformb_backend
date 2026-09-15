<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Invoice\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'emission_point_id' => ['required', 'string'],
            'establishment_code' => ['required', 'string'],
            'emission_point_code' => ['required', 'string'],
            'recipient' => ['required', 'array'],
            'issuer' => ['required', 'array'],
            'lines' => ['required', 'array'],
            'lines.*.product_id' => ['nullable', 'string'],
            'lines.*.description_snapshot' => ['nullable', 'string'],
            'lines.*.quantity' => ['required', 'numeric'],
            'lines.*.unit_price' => ['required', 'numeric'],
            'lines.*.subtotal' => ['required', 'numeric'],
            'lines.*.tax' => ['nullable', 'numeric'],
            'lines.*.discount' => ['nullable', 'numeric'],
            'lines.*.total' => ['nullable', 'numeric'],
            'payments' => ['nullable', 'array'],
            'payments.*.payment_method_code' => ['required', 'string'],
            'payments.*.total' => ['required', 'numeric'],
            'subtotal' => ['required', 'numeric'],
            'tax' => ['required', 'numeric'],
            'total' => ['required', 'numeric'],
            'discount' => ['nullable', 'numeric'],
            'due_at' => ['nullable', 'string'],
            'idempotency_key' => ['nullable', 'string'],
        ];
    }
}
