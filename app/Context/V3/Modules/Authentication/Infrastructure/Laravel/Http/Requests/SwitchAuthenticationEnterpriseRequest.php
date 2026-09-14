<?php

namespace App\Context\V3\Modules\Authentication\Infrastructure\Laravel\Http\Requests;

use App\Context\V3\Modules\Authentication\Application\DTOs\SwitchEnterpriseDTO;
use Illuminate\Foundation\Http\FormRequest;

final class SwitchAuthenticationEnterpriseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['enterprise_id' => ['required', 'string', 'max:36']];
    }

    public function switchEnterprise(): SwitchEnterpriseDTO
    {
        return SwitchEnterpriseDTO::fromArray($this->validated());
    }
}
