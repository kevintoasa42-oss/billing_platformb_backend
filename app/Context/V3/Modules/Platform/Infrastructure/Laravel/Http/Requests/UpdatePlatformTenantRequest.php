<?php

namespace App\Context\V3\Modules\Platform\Infrastructure\Laravel\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdatePlatformTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'ruc' => ['sometimes', 'string', 'max:32'],
            'synthetic' => ['sometimes', 'boolean'],
        ];
    }
}
