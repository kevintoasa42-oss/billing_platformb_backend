<?php

namespace App\Context\V3\Modules\Core\Carrier\Domain\Repository;

use App\Context\V3\Modules\Core\Carrier\Domain\Models\CarrierEstablishment;

interface CarrierEstablishmentRepositoryInterface
{
    /**
     * @return array<int, CarrierEstablishment>
     */
    public function all(): array;

    public function find(string $id): ?CarrierEstablishment;

    public function create(CarrierEstablishment $establishment): CarrierEstablishment;
}
