<?php

namespace App\Context\V1\Modules\Product\Application\UseCases;

use App\Context\V1\Modules\Product\Application\DTOs\ProductDTO;
use App\Context\V1\Modules\Product\Domain\Mappers\ProductMapper;
use App\Context\V1\Modules\Product\Domain\Repositories\ProductRepositoryInterface;

class UpdateProductUseCase
{
    public function __construct(
        private ProductRepositoryInterface $repository,
    ) {}

    public function execute(ProductDTO $dto): ProductDTO
    {
        $product = ProductMapper::fromDto($dto);

        return ProductDTO::fromArray($this->repository->update($product));
    }
}
