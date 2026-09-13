<?php

namespace App\Context\Enterprise\Domain\Models;

class Role
{
    public function __construct(
        public ?int $id = null,
        public ?string $nombre = null,
        public ?string $descripcion = null,
    ) {}
}
