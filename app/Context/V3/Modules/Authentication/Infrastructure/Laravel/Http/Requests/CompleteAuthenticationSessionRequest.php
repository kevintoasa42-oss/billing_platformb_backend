<?php

namespace App\Context\V3\Modules\Authentication\Infrastructure\Laravel\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CompleteAuthenticationSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['enterprise_id' => ['required', 'string', 'max:36']];
    }
}
