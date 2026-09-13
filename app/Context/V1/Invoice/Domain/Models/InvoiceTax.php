<?php

namespace App\Context\V1\Invoice\Domain\Models;

class InvoiceTax
{
    public function __construct(
        public ?int $id = null,
        public ?int $invoice_header_id = null,
        public ?int $sri_iva_percentage_id = null,
        public ?string $code = null,
        public ?string $percentage_code = null,
        public float $rate = 0,
        public float $tax_base = 0,
        public float $tax = 0,
    ) {}
}
