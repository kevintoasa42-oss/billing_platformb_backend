<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\Carrier\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CarrierAllocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string,mixed> */
    public function rules(): array
    {
        return [
            'operation_id' => ['required', 'uuid'],
            'received_document_id' => ['required', 'uuid'],
            'amount' => ['required', 'regex:/^(?:0|[1-9][0-9]*)(?:\.[0-9]{1,2})?$/'],
        ];
    }
}
