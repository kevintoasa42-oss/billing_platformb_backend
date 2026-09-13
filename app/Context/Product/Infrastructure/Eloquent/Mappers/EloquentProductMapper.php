<?php

namespace App\Context\Product\Infrastructure\Eloquent\Mappers;

use App\Context\Product\Domain\Mappers\ProductMapperInterface;
use App\Context\Product\Domain\Models\Product;
use App\Context\Product\Infrastructure\Eloquent\Models\ProductModel;

class EloquentProductMapper implements ProductMapperInterface
{
    public function toDomain(object $model): Product
    {
        /** @var ProductModel $model */
        return new Product(
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

    public function toModel(Product $product): array
    {
        return [
            'codigo_barras' => $product->codigo_barras,
            'codigo_auxiliar' => $product->codigo_auxiliar,
            'nombre' => $product->nombre,
            'descripcion' => $product->descripcion,
            'estado' => $product->estado,
            'precio_base' => $product->precio_base,
        ];
    }
}
