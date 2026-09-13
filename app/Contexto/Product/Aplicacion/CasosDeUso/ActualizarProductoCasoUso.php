<?php

namespace App\Contexto\Product\Aplicacion\CasosDeUso;

use App\Contexto\Product\Aplicacion\DTOs\ProductoDTO;
use App\Contexto\Product\Dominio\Modelos\Producto;
use App\Contexto\Product\Dominio\Repositorios\ProductoRepositoryInterface;

class ActualizarProductoCasoUso
{
    public function __construct(
        private ProductoRepositoryInterface $repository,
    ) {}

    public function ejecutar(ProductoDTO $dto): ProductoDTO
    {
        $producto = new Producto(
            id: $dto->id,
            codigo_barras: $dto->codigo_barras,
            codigo_auxiliar: $dto->codigo_auxiliar,
            nombre: $dto->nombre,
            descripcion: $dto->descripcion,
            estado: $dto->estado,
            precio_base: $dto->precio_base,
            impuestos: $dto->impuestos,
        );

        $producto = $this->repository->actualizar($producto);

        return ProductoDTO::fromArray([
            'id' => $producto->id,
            'codigo_barras' => $producto->codigo_barras,
            'codigo_auxiliar' => $producto->codigo_auxiliar,
            'nombre' => $producto->nombre,
            'descripcion' => $producto->descripcion,
            'estado' => $producto->estado,
            'precio_base' => $producto->precio_base,
            'impuestos' => $producto->impuestos,
        ]);
    }
}
