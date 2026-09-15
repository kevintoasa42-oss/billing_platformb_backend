<?php

namespace App\Context\V3\Modules\Core\Product\Domain\Repository;

use App\Context\V3\Modules\Core\Product\Domain\Models\Product;

interface ProductRepositoryInterface
{
    /**
     * @return Product[]
     */
    public function all(?string $search = null, int $limit = 500, ?bool $isActive = null): array;

    public function findByLegacyId(int $legacyId): ?Product;

    public function create(array $data): Product;

    public function update(int $legacyId, array $data): ?Product;

    public function setActive(int $legacyId, bool $isActive): ?Product;

    public function delete(int $legacyId): ?Product;

    /**
     * @return array<string, bool>
     */
    public function duplicates(array $filters): array;
}
