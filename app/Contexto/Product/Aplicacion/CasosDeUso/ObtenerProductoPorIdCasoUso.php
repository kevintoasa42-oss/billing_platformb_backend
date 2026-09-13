<?php

namespace App\Contexto\Product\Aplicacion\CasosDeUso;

use App\Contexto\Product\Aplicacion\DTOs\ProductoDTO;
use App\Contexto\Product\Dominio\Repositorios\ProductoRepositoryInterface;

class ObtenerProductoPorIdCasoUso
{
    public function __construct(
        private ProductoRepositoryInterface $repository,
    ) {}

    public function ejecutar(int $id): ?ProductoDTO
    {
        $producto = $this->repository->obtenerPorId($id);

        if (!$producto) {
            return null;
        }

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
