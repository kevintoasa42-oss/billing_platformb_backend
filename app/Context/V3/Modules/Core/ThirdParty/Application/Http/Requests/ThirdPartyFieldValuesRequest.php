<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ThirdPartyFieldValuesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'custom_fields' => ['required', 'array', 'max:100'],
        ];
    }
}
