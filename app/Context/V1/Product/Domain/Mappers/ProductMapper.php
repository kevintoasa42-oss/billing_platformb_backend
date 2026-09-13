<?php

namespace App\Context\V1\Product\Domain\Mappers;

use App\Context\V1\Product\Domain\Models\Product;

class ProductMapper
{
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
