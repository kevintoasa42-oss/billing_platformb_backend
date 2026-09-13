<?php

namespace App\Context\V1\Product\Application\UseCases;

use App\Context\V1\Product\Application\DTOs\ProductDTO;
use App\Context\V1\Product\Domain\Repositories\ProductRepositoryInterface;

class GetProductByIdUseCase
{
    public function __construct(
        private ProductRepositoryInterface $repository,
    ) {}

    public function ejecutar(int $id): ?ProductDTO
    {
        $product = $this->repository->obtenerPorId($id);

        if (!$product) {
            return null;
        }

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
