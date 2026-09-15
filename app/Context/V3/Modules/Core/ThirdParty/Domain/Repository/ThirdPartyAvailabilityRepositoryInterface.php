<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Domain\Repository;

use App\Context\V3\Modules\Core\ThirdParty\Domain\Models\ThirdPartyIdentity;

interface ThirdPartyAvailabilityRepositoryInterface
{
    public function findByIdentification(string $identification, string $identificationType): ?ThirdPartyIdentity;
}
