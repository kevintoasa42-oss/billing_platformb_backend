<?php

namespace App\Context\Product\Domain\Mappers;

use App\Context\Product\Domain\Models\Product;

interface ProductMapperInterface
{
    public function toDomain(object $model): Product;

    public function toModel(Product $product): array;
}
