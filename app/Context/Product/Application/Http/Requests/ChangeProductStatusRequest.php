<?php

namespace App\Context\Product\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangeProductStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'estado' => 'required|boolean',
        ];
    }
}
