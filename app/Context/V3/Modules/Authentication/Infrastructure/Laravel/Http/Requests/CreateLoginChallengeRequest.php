<?php

namespace App\Context\V3\Modules\Authentication\Infrastructure\Laravel\Http\Requests;

use App\Context\V3\Modules\Authentication\Application\DTOs\CredentialsDTO;
use Illuminate\Foundation\Http\FormRequest;

final class CreateLoginChallengeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ];
    }

    public function credentials(): CredentialsDTO
    {
        return CredentialsDTO::fromArray($this->validated());
    }
}
