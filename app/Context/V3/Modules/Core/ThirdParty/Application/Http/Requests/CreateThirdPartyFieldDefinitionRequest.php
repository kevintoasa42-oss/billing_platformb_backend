<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Application\Http\Requests;

final class CreateThirdPartyFieldDefinitionRequest extends AbstractThirdPartyFieldDefinitionRequest
{
    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return $this->fieldRules(true);
    }
}
