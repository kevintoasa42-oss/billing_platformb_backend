<?php

namespace App\Context\V3\Modules\Core\Carrier\Domain\Repository;

use App\Context\V3\Modules\Core\Carrier\Domain\Models\CarrierEmissionPoint;

interface CarrierEmissionPointRepositoryInterface
{
    /**
     * @return array<int, CarrierEmissionPoint>
     */
    public function all(): array;

    public function find(string $id): ?CarrierEmissionPoint;

    /**
     * @return array<int, CarrierEmissionPoint>
     */
    public function byEstablishment(string $establishmentId): array;

    public function create(CarrierEmissionPoint $emissionPoint): CarrierEmissionPoint;
}
