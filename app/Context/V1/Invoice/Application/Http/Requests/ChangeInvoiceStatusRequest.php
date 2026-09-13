<?php

namespace App\Context\V1\Invoice\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangeInvoiceStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|string|in:PENDIENTE,RECHAZADO,AUTORIZADO',
        ];
    }
}
