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
            barcode: $model->barcode,
            auxiliary_code: $model->auxiliary_code,
            name: $model->name,
            description: $model->description,
            status: $model->status,
            base_price: (float) $model->base_price,
            impuestos: $model->getImpuestoIds(),
        );
    }

    public function toModel(Product $product): array
    {
        return [
            'barcode' => $product->barcode,
            'auxiliary_code' => $product->auxiliary_code,
            'name' => $product->name,
            'description' => $product->description,
            'status' => $product->status,
            'base_price' => $product->base_price,
        ];
    }
}
