<?php

namespace App\Context\V1\Partners\Domain\Models;

/** Framework-independent partner aggregate. */
final class Partner
{
    public function __construct(
        public ?int $id = null,
        public ?string $identification_type = null,
        public ?string $identification_number = null,
        public ?string $name = null,
        public ?string $last_name = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $address = null,
        public bool $status = false,
        public ?string $created_at = null,
        public ?string $updated_at = null,
        public ?string $deleted_at = null,
    ) {}
}
