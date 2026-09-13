<?php

namespace App\Context\V1\Modules\Carrier\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangeCarrierStatusRequest extends FormRequest
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
