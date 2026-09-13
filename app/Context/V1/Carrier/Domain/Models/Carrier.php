<?php

namespace App\Context\V1\Carrier\Domain\Models;

class Carrier
{
    public function __construct(
        public ?int $id = null,
        public ?string $ruc = null,
        public ?string $name = null,
        public ?string $tradename = null,
        public ?string $matrix_address = null,
        public ?string $special_taxpayer = null,
        public bool $accounting_required = false,
        public bool $status = true,
    ) {}
}
