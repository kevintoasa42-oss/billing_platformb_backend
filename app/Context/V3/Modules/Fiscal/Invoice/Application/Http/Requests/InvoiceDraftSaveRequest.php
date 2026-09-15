<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Invoice\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceDraftSaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payload' => ['required', 'array'],
            'revision' => ['nullable', 'integer'],
            'public_id' => ['nullable', 'string'],
        ];
    }
}
