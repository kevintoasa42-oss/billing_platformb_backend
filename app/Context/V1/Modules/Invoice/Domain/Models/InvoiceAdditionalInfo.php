<?php

namespace App\Context\V1\Modules\Invoice\Domain\Models;

class InvoiceAdditionalInfo
{
    public function __construct(
        public ?int $id = null,
        public ?int $invoice_header_id = null,
        public ?string $name = null,
        public ?string $value = null,
    ) {}
}
