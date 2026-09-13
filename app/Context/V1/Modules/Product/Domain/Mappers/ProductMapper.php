<?php

namespace App\Context\V1\Modules\Product\Domain\Mappers;

use App\Context\V1\Modules\Product\Application\DTOs\ProductDTO;
use App\Context\V1\Modules\Product\Domain\Models\Product;

class ProductMapper
{
    public static function fromDto(ProductDTO $dto): Product
    {
        return new Product(
            id: $dto->id,
            barcode: $dto->barcode,
            auxiliary_code: $dto->auxiliary_code,
            name: $dto->name,
            description: $dto->description,
            status: $dto->status,
            base_price: $dto->base_price,
            taxes: $dto->taxes,
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
}
