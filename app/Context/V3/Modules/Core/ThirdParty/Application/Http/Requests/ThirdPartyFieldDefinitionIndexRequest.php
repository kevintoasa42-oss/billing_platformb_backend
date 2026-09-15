<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ThirdPartyFieldDefinitionIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $scope = $this->query('scope');

        if (is_string($scope)) {
            $this->merge(['scope' => strtolower(trim($scope))]);
        }
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'scope' => ['nullable', 'string', 'in:customer,carrier,both'],
            'active_only' => ['nullable', 'boolean'],
        ];
    }
}
