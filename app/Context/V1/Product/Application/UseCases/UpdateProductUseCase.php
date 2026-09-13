<?php

namespace App\Context\V1\Product\Application\UseCases;

use App\Context\V1\Product\Application\DTOs\ProductDTO;
use App\Context\V1\Product\Domain\Models\Product;
use App\Context\V1\Product\Domain\Repositories\ProductRepositoryInterface;

class UpdateProductUseCase
{
    public function __construct(
        private ProductRepositoryInterface $repository,
    ) {}

    public function ejecutar(ProductDTO $dto): ProductDTO
    {
        $product = new Product(
            id: $dto->id,
            barcode: $dto->barcode,
            auxiliary_code: $dto->auxiliary_code,
            name: $dto->name,
            description: $dto->description,
            status: $dto->status,
            base_price: $dto->base_price,
            taxes: $dto->taxes,
        );

        $product = $this->repository->actualizar($product);

        return ProductDTO::fromArray([
            'id' => $product->id,
            'barcode' => $product->barcode,
            'auxiliary_code' => $product->auxiliary_code,
            'name' => $product->name,
            'description' => $product->description,
            'status' => $product->status,
            'base_price' => $product->base_price,
            'taxes' => $product->taxes,
        ]);
    }
}
