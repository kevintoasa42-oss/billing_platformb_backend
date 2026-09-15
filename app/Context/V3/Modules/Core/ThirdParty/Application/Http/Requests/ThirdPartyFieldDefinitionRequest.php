<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ThirdPartyFieldDefinitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => is_string($this->input('code')) ? strtolower(trim($this->input('code'))) : $this->input('code'),
            'label' => is_string($this->input('label')) ? trim($this->input('label')) : $this->input('label'),
            'scope' => is_string($this->input('scope')) ? strtolower(trim($this->input('scope'))) : ($this->input('scope') ?? 'both'),
            'data_type' => is_string($this->input('data_type')) ? strtolower(trim($this->input('data_type'))) : ($this->input('data_type') ?? 'text'),
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'code' => ['sometimes', 'required', 'string', 'max:81', 'regex:/^[a-z][a-z0-9_]{1,80}$/'],
            'label' => ['sometimes', 'required', 'string', 'max:160'],
            'scope' => ['sometimes', 'in:customer,carrier,both'],
            'data_type' => ['sometimes', 'in:text,number,date,boolean,select'],
            'validation' => ['sometimes', 'array'],
            'validation.options' => ['sometimes', 'array', 'min:1', 'max:100'],
            'validation.options.*' => ['string', 'max:160'],
            'validation.min_length' => ['sometimes', 'integer', 'min:0', 'max:10000'],
            'validation.max_length' => ['sometimes', 'integer', 'min:0', 'max:10000'],
            'validation.min' => ['sometimes', 'numeric'],
            'validation.max' => ['sometimes', 'numeric'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:100000'],
            'is_required' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
