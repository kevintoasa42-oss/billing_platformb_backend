<?php

namespace App\Context\V3\Modules\Core\Settings\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdditionalInfoPresetUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['nullable', 'string', 'max:50'],
            'name' => ['nullable', 'string', 'max:150'],
            'default_value' => ['nullable', 'string', 'max:500'],
            'auto_apply' => ['nullable', 'boolean'],
            'value_editable' => ['nullable', 'boolean'],
            'is_required' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'access_rules' => ['nullable', 'array'],
        ];
    }
}
