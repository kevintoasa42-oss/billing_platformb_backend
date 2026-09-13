<?php

namespace App\Context\V1\Product\Infrastructure\Eloquent\Mappers;

use App\Context\V1\Product\Domain\Models\Product;
use App\Models\ProductModel;

class EloquentProductMapper
{
    public static function toDomain(object $model): Product
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
            taxes: $model->getTaxIds(),
        );
    }

    public static function toModel(Product $product): array
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
