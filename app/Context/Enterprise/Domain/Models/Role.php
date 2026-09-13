<?php

namespace App\Context\Enterprise\Domain\Models;

class Role
{
    public function __construct(
        public ?int $id = null,
        public ?string $name = null,
        public ?string $description = null,
    ) {}
}
