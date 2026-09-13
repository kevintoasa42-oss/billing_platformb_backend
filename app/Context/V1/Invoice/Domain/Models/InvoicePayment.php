<?php

namespace App\Context\V1\Invoice\Domain\Models;

class InvoicePayment
{
    public function __construct(
        public ?int $id = null,
        public ?int $invoice_header_id = null,
        public ?int $sri_payment_method_id = null,
        public ?string $payment_code = null,
        public float $total = 0,
        public int $term = 0,
    ) {}
}
