<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\Carrier\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CarrierReceivedDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string,mixed> */
    public function rules(): array
    {
        return [
            'document_type' => ['required', 'in:invoice,credit_note,debit_note,other'],
            'reference' => ['required', 'string', 'max:120'],
            'issue_date' => ['required', 'date_format:Y-m-d'],
            'total' => ['required', 'regex:/^(?:0|[1-9][0-9]*)(?:\.[0-9]{1,2})?$/'],
            'xml_base64' => ['required', 'string', 'max:14000000'],
            'operation_id' => ['nullable', 'uuid'],
            'original_document_id' => ['nullable', 'uuid'],
            'reason' => ['nullable', 'string', 'max:500'],
            'fiscal_status' => ['nullable', 'in:draft,received,authorized,imported'],
            'access_key' => ['nullable', 'string', 'max:100'],
            'authorization_number' => ['nullable', 'string', 'max:100'],
            'affects_transport' => ['nullable', 'boolean'],
        ];
    }
}
