<?php

namespace App\Context\V1\Product\Application\UseCases;

use App\Context\V1\Product\Application\DTOs\ProductDTO;
use App\Context\V1\Product\Domain\Mappers\ProductMapper;
use App\Context\V1\Product\Domain\Repositories\ProductRepositoryInterface;

class CreateProductUseCase
{
    public function __construct(
        private ProductRepositoryInterface $repository,
    ) {}

    public function execute(ProductDTO $dto): ProductDTO
    {
        $product = ProductMapper::fromDto($dto);

        return ProductDTO::fromArray($this->repository->create($product));
    }
}
