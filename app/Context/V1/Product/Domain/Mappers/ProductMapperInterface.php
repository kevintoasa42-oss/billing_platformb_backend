<?php

namespace App\Context\V1\Product\Domain\Mappers;

use App\Context\V1\Product\Domain\Models\Product;

interface ProductMapperInterface
{
    public function toDomain(object $model): Product;

    public function toModel(Product $product): array;
}
