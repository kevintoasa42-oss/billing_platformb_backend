<?php

namespace App\Context\V1\Modules\Invoice\Domain\Models;

class InvoiceItem
{
    public function __construct(
        public ?int $id = null,
        public ?int $invoice_header_id = null,
        public ?int $product_id = null,
        public ?string $main_code = null,
        public ?string $auxiliary_code = null,
        public ?string $description = null,
        public float $quantity = 0,
        public float $unit_price = 0,
        public float $discount = 0,
        public float $tax_base = 0,
        /** @var InvoiceItemTax[] */
        public array $taxes = [],
    ) {}
}
