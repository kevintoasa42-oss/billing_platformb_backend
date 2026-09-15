<?php

namespace App\Context\V3\Modules\Core\Product\Domain\Repository;

use App\Context\V3\Modules\Core\Product\Domain\Models\ProductTax;

interface ProductTaxRepositoryInterface
{
    public function create(int $productLegacyId, array $data): ?ProductTax;

    public function delete(int $productLegacyId, int $taxId): bool;

    public function findByProductAndType(int $productLegacyId, int $sriIvaTypeId): ?ProductTax;
}
