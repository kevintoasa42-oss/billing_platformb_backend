<?php

namespace App\Context\V3\Modules\Core\Settings\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerSettingsUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'allow_multiple_plates' => ['nullable', 'boolean'],
        ];
    }
}
