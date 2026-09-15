<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Application\Http\Requests;

final class UpdateThirdPartyFieldDefinitionRequest extends AbstractThirdPartyFieldDefinitionRequest
{
    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return $this->fieldRules(false);
    }
}
