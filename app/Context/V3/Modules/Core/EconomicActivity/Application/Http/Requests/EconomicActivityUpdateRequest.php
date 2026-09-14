<?php

namespace App\Context\V3\Modules\Core\EconomicActivity\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EconomicActivityUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'catalog_version' => ['nullable', 'string', 'in:synthetic-lab-v1,staging-legacy-v1'],
        ];
    }
}
