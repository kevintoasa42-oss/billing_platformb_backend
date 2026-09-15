<?php

namespace App\Context\V3\Modules\Core\Product\Application\UseCases;

use App\Context\V3\Modules\Core\Product\Application\DTOs\ProductCreateDTO;
use App\Context\V3\Modules\Core\Product\Application\DTOs\ProductUpdateDTO;
use App\Context\V3\Modules\Core\Product\Domain\Models\Product;
use App\Context\V3\Modules\Core\Product\Domain\Repository\ProductRepositoryInterface;

class ProductUseCase
{
    public function __construct(
        private readonly ProductRepositoryInterface $repository,
    ) {}

    /**
     * @return Product[]
     */
    public function all(?string $search = null, int $limit = 500, ?bool $isActive = null): array
    {
        return $this->repository->all($search, $limit, $isActive);
    }

    public function find(int $legacyId): ?Product
    {
        return $this->repository->findByLegacyId($legacyId);
    }

    public function create(ProductCreateDTO $dto): Product
    {
        return $this->repository->create($dto->toDatabaseArray());
    }

    public function update(int $legacyId, ProductUpdateDTO $dto): ?Product
    {
        return $this->repository->update($legacyId, $dto->toDatabaseArray());
    }

    public function setActive(int $legacyId, bool $isActive): ?Product
    {
        return $this->repository->setActive($legacyId, $isActive);
    }

    public function delete(int $legacyId): ?Product
    {
        return $this->repository->delete($legacyId);
    }

    /**
     * @return array<string, bool>
     */
    public function duplicates(array $filters): array
    {
        return $this->repository->duplicates($filters);
    }
}
