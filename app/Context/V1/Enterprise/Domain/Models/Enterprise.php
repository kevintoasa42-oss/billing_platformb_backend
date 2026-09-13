<?php

namespace App\Context\V1\Enterprise\Domain\Models;

class Enterprise
{
    public function __construct(
        public ?int $id = null,
        public ?string $name = null,
        public ?string $ruc = null,
        public ?string $tradename = null,
        public ?string $matrix_name = null,
        public ?string $phone = null,
        public ?string $corporate_email = null,
        public ?string $db_name = null,
    ) {}

    public function getDbName(): ?string
    {
        return $this->db_name ?? $this->ruc;
    }
}
