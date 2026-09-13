<?php

namespace App\Context\Enterprise\Domain\Models;

class User
{
    public function __construct(
        public ?int $id = null,
        public ?string $nombre = null,
        public ?string $email = null,
        public ?string $password = null,
        /** @var Role[] */
        public array $roles = [],
        /** @var Enterprise[] */
        public array $enterprises = [],
    ) {}
}
