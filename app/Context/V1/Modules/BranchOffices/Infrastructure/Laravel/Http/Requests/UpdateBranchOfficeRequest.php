<?php

namespace App\Context\V1\Modules\BranchOffices\Infrastructure\Laravel\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateBranchOfficeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'], 'code_sri' => ['sometimes', 'string', 'max:20'],
            'status' => ['sometimes', 'boolean'], 'type' => ['sometimes', 'string', 'max:100'],
            'default' => ['sometimes', 'boolean'],
        ];
    }
}
