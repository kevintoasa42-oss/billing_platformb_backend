<?php

namespace App\Context\V1\Product\Application\UseCases;

use App\Context\V1\Product\Application\DTOs\ProductDTO;
use App\Context\V1\Product\Domain\Repositories\ProductRepositoryInterface;
use App\Context\V1\Product\Infrastructure\Eloquent\Mappers\EloquentProductMapper;

class GetProductByIdUseCase
{
    public function __construct(
        private ProductRepositoryInterface $repository,
    ) {}

    public function execute(int $id): ?ProductDTO
    {
        $product = $this->repository->getById($id);

        if (!$product) {
            return null;
        }

        return ProductDTO::fromArray(EloquentProductMapper::toDtoArray($product));
    }
}
