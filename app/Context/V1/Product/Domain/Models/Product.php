<?php

namespace App\Context\V1\Product\Domain\Models;

class Product
{
    public function __construct(
        public ?int $id = null,
        public ?string $barcode = null,
        public ?string $auxiliary_code = null,
        public ?string $name = null,
        public ?string $description = null,
        public bool $status = true,
        public float $base_price = 0,
        /** @var int[] IDs de sri_iva_percentages (DB central) */
        public array $taxes = [],
    ) {}
}
