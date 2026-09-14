<?php

namespace App\Context\V3\Modules\Core\Carrier\Domain\Repository;

use App\Context\V3\Modules\Core\Carrier\Domain\Models\CarrierEstablishment;

interface CarrierEstablishmentRepositoryInterface
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
     * @return array<string, mixed>
     */
    public function create(CarrierEstablishment $establishment): array;
}
