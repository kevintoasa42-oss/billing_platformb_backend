<?php

namespace App\Context\V3\Modules\Core\Product\Application\UseCases;

use App\Context\V3\Modules\Core\Product\Application\DTOs\ProductTaxCreateDTO;
use App\Context\V3\Modules\Core\Product\Domain\Models\ProductTax;
use App\Context\V3\Modules\Core\Product\Domain\Repository\ProductTaxRepositoryInterface;
use RuntimeException;

class ProductTaxUseCase
{
    public function __construct(
        private readonly ProductTaxRepositoryInterface $repository,
    ) {}

    public function create(int $productLegacyId, ProductTaxCreateDTO $dto): ProductTax
    {
        $existing = $this->repository->findByProductAndType($productLegacyId, $dto->sriIvaTypeId);
        if ($existing !== null) {
            throw new RuntimeException('This tax type is already assigned to the product.');
        }

        $tax = $this->repository->create($productLegacyId, $dto->toArray());

        if ($tax === null) {
            throw new RuntimeException('No se encontró el producto solicitado.');
        }

        return $tax;
    }

    public function delete(int $productLegacyId, int $taxId): bool
    {
        return $this->repository->delete($productLegacyId, $taxId);
    }
}
