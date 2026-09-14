<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Invoice\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceVoidRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason_code' => ['nullable', 'string'],
            'reason_note' => ['nullable', 'string'],
        ];
    }
}
