<?php

namespace App\Context\V1\Clients\Domain\Models;

/** Framework-independent client aggregate. */
final class Client
{
    public function __construct(
        public ?int $id = null,
        public ?string $identification_type = null,
        public ?string $identification_number = null,
        public ?string $name = null,
        public ?string $last_name = null,
        public ?string $status = null,
        public ?string $address = null,
        public ?string $phone = null,
        public ?string $email = null,
        public ?string $type = null,
        public ?string $plates = null,
        public ?string $created_at = null,
        public ?string $updated_at = null,
        public ?string $deleted_at = null,
    ) {}
}
