<?php

namespace App\Context\Product\Application\UseCases;

use App\Context\Product\Application\DTOs\ProductDTO;
use App\Context\Product\Domain\Models\Product;
use App\Context\Product\Domain\Repositories\ProductRepositoryInterface;

class UpdateProductUseCase
{
    public function __construct(
        private ProductRepositoryInterface $repository,
    ) {}

    public function ejecutar(ProductDTO $dto): ProductDTO
    {
        $product = new Product(
            id: $dto->id,
            codigo_barras: $dto->codigo_barras,
            codigo_auxiliar: $dto->codigo_auxiliar,
            nombre: $dto->nombre,
            descripcion: $dto->descripcion,
            estado: $dto->estado,
            precio_base: $dto->precio_base,
            impuestos: $dto->impuestos,
        );

        $product = $this->repository->actualizar($product);

        return ProductDTO::fromArray([
            'id' => $product->id,
            'codigo_barras' => $product->codigo_barras,
            'codigo_auxiliar' => $product->codigo_auxiliar,
            'nombre' => $product->nombre,
            'descripcion' => $product->descripcion,
            'estado' => $product->estado,
            'precio_base' => $product->precio_base,
            'impuestos' => $product->impuestos,
        ]);
    }
}
