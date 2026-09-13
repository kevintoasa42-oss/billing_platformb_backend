<?php

namespace App\Context\Product\Application\UseCases;

use App\Context\Product\Application\DTOs\ProductDTO;
use App\Context\Product\Domain\Repositories\ProductRepositoryInterface;

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
            'impuestos' => $product->impuestos,
        ]);
    }
}
