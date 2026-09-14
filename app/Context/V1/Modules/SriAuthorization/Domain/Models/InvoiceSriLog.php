<?php

namespace App\Context\V1\Modules\SriAuthorization\Domain\Models;

class InvoiceSriLog
{
    public function __construct(
        public ?int $id = null,
        public int $invoice_header_id,
        public string $access_key,
        public string $operation_type, // reception | authorization
        public bool $status,
        public ?string $sri_state = null,
        public ?string $response_message = null,
        public ?array $raw_response = null,
        public ?string $environment = null,
        public ?string $authorization_date = null,
    ) {}
}
