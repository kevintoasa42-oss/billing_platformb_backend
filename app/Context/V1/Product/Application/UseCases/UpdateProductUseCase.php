<?php

namespace App\Context\V1\Product\Application\UseCases;

use App\Context\V1\Product\Application\DTOs\ProductDTO;
use App\Context\V1\Product\Domain\Mappers\ProductMapper;
use App\Context\V1\Product\Domain\Repositories\ProductRepositoryInterface;

class UpdateProductUseCase
{
    public function __construct(
        private ProductRepositoryInterface $repository,
    ) {}

    public function execute(ProductDTO $dto): ProductDTO
    {
        $product = ProductMapper::fromDto([
            'id' => $dto->id,
            'barcode' => $dto->barcode,
            'auxiliary_code' => $dto->auxiliary_code,
            'name' => $dto->name,
            'description' => $dto->description,
            'status' => $dto->status,
            'base_price' => $dto->base_price,
            'taxes' => $dto->taxes,
        ]);

        $product = $this->repository->update($product);

        return ProductDTO::fromArray(ProductMapper::toDtoArray($product));
    }
}
