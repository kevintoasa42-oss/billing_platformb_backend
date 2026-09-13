<?php

namespace App\Context\V1\SriVoucherTypes\Domain\Models;

/** Framework-independent SRI voucher-type catalogue entity. */
final class SriVoucherType
{
    public function __construct(
        public ?int $id = null,
        public ?string $document = null,
        public ?string $code = null,
        public ?string $sustentation_code = null,
        public ?string $start_date = null,
        public ?string $end_date = null,
        public bool $retention = false,
        public ?string $created_at = null,
        public ?string $updated_at = null,
        public ?string $deleted_at = null,
    ) {}
}
