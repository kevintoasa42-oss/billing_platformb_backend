<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Domain\Repository;

use App\Context\V3\Modules\Core\ThirdParty\Domain\Models\ThirdParty;

interface ThirdPartyRepositoryInterface
{
    public function identificationExists(string $identification, string $identificationType): bool;

    public function create(ThirdParty $thirdParty): ThirdParty;
}
