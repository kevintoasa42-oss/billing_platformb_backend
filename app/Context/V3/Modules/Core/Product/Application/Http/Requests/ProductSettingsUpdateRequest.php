<?php

namespace App\Context\V3\Modules\Core\Product\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductSettingsUpdateRequest extends FormRequest
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
            'allow_duplicate_names' => ['required', 'boolean'],
            'require_barcode' => ['required', 'boolean'],
            'require_auxiliary_code' => ['required', 'boolean'],
            'auxiliary_code_prefix' => ['nullable', 'string', 'max:20', 'regex:/^[A-Za-z0-9._-]*$/'],
            'default_product_type' => ['required', Rule::in(['product', 'service'])],
            'default_iva_type_id' => ['nullable', 'integer'],
            'require_description' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'auxiliary_code_prefix' => trim((string) $this->input('auxiliary_code_prefix', '')),
        ]);
    }
}
