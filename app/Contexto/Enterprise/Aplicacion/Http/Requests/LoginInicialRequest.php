<?php

namespace App\Contexto\Enterprise\Aplicacion\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginInicialRequest extends FormRequest
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
