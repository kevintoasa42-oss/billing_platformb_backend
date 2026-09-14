<?php

namespace App\Context\V1\Modules\Enterprise\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InitialLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string',
        ];
    }
}
