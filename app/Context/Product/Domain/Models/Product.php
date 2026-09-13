<?php

namespace App\Context\Product\Domain\Models;

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
        public array $impuestos = [],
    ) {}

    public function activar(): void
    {
        $this->status = true;
    }

    public function desactivar(): void
    {
        $this->status = false;
    }
}
