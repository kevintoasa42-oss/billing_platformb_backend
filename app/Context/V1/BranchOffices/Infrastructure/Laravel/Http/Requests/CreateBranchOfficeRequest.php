<?php

namespace App\Context\V1\BranchOffices\Infrastructure\Laravel\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CreateBranchOfficeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'], 'code_sri' => ['required', 'string', 'max:20'],
            'status' => ['sometimes', 'boolean'], 'type' => ['required', 'string', 'max:100'],
            'default' => ['sometimes', 'boolean'],
        ];
    }
}
