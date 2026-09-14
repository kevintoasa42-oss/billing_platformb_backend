<?php

namespace App\Context\V3\Modules\Core\Carrier\Domain\Repository;

use App\Context\V3\Modules\Core\Carrier\Domain\Models\CarrierCompany;

interface CarrierCompanyRepositoryInterface
{
    public function create(CarrierCompany $company): CarrierCompany;

    public function findByThirdPartyId(string $thirdPartyId): ?CarrierCompany;
}
