<?php

namespace App\Context\V3\Modules\Core\Company\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanyCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'ruc' => ['required', 'string', 'max:13'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'trade_name' => ['nullable', 'string', 'max:255'],
            'matrix_address' => ['nullable', 'string', 'max:255'],
            'operations_start_date' => ['nullable', 'date'],
            'city_id' => ['nullable', 'integer'],
            'phone' => ['nullable', 'string', 'max:30'],
            'corporate_email' => ['nullable', 'email', 'max:150'],
            'activity_ids' => ['nullable', 'array'],
            'activity_ids.*' => ['string', 'max:100'],
        ];
    }
}
