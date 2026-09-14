<?php

namespace App\Context\V3\Modules\Core\Carrier\Domain\Repository;

use App\Context\V3\Modules\Core\Carrier\Domain\Models\CarrierEmissionPoint;

interface CarrierEmissionPointRepositoryInterface
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array;

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $id): ?array;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function byEstablishment(string $establishmentId): array;

    /**
     * @return array<string, mixed>
     */
    public function create(CarrierEmissionPoint $emissionPoint): array;
}
