<?php

namespace App\Contexto\Product\Infraestructura\Eloquent\Mappers;

use App\Contexto\Product\Dominio\Mappers\ProductoMapperInterface;
use App\Contexto\Product\Dominio\Modelos\Producto;
use App\Contexto\Product\Infraestructura\Eloquent\Models\ProductoModel;

class EloquentProductoMapper implements ProductoMapperInterface
{
    public function toDomain(object $model): Producto
    {
        /** @var ProductoModel $model */
        return new Producto(
            id: $model->id,
            codigo_barras: $model->codigo_barras,
            codigo_auxiliar: $model->codigo_auxiliar,
            nombre: $model->nombre,
            descripcion: $model->descripcion,
            estado: $model->estado,
            precio_base: (float) $model->precio_base,
            impuestos: $model->getImpuestoIds(),
        );
    }

    public function toModel(Producto $producto): array
    {
        return [
            'codigo_barras' => $producto->codigo_barras,
            'codigo_auxiliar' => $producto->codigo_auxiliar,
            'nombre' => $producto->nombre,
            'descripcion' => $producto->descripcion,
            'estado' => $producto->estado,
            'precio_base' => $producto->precio_base,
        ];
    }
}
