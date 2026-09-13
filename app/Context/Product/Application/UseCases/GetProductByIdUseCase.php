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
