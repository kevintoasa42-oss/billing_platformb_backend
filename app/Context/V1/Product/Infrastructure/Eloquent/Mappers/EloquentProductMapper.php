<?php

namespace App\Context\V1\Product\Infrastructure\Eloquent\Mappers;

use App\Context\V1\Product\Domain\Mappers\ProductMapperInterface;
use App\Context\V1\Product\Domain\Models\Product;
use App\Models\ProductModel;

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
            taxes: $model->getTaxIds(),
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

    /**
     * Helper methods to build/modify a Product domain model.
     */

    public static function fromDto(array $data): Product
    {
        return new Product(
            id: $data['id'] ?? null,
            barcode: $data['barcode'] ?? null,
            auxiliary_code: $data['auxiliary_code'] ?? null,
            name: $data['name'] ?? null,
            description: $data['description'] ?? null,
            status: $data['status'] ?? true,
            base_price: $data['base_price'] ?? 0,
            taxes: $data['taxes'] ?? [],
        );
    }

    public static function toDtoArray(Product $product): array
    {
        return [
            'id' => $product->id,
            'barcode' => $product->barcode,
            'auxiliary_code' => $product->auxiliary_code,
            'name' => $product->name,
            'description' => $product->description,
            'status' => $product->status,
            'base_price' => $product->base_price,
            'taxes' => $product->taxes,
        ];
    }

    public static function activate(Product $product): void
    {
        $product->status = true;
    }

    public static function deactivate(Product $product): void
    {
        $product->status = false;
    }
}
