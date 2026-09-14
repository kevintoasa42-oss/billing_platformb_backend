<?php

namespace App\Context\V3\Modules\Core\SriIva\Domain\Repository;

use App\Context\V3\Modules\Core\SriIva\Domain\Models\SriIvaPercentage;

interface SriIvaPercentageRepositoryInterface
{
    /**
     * @return SriIvaPercentage[]
     */
    public function findByType(int $sriIvaTypeId): array;

    public function find(int $id): ?SriIvaPercentage;

    public function create(array $data): SriIvaPercentage;

    public function update(int $id, array $data): ?SriIvaPercentage;

    public function delete(int $id): bool;
}
