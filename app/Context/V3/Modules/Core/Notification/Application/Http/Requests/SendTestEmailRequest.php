<?php

namespace App\Context\V3\Modules\Core\Notification\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendTestEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'to' => ['required', 'email', 'max:150'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'is_html' => ['nullable', 'boolean'],
        ];
    }
}
