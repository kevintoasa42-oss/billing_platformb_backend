<?php

namespace App\Context\V1\Modules\Signature\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangeSignatureStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|boolean',
        ];
    }
}
