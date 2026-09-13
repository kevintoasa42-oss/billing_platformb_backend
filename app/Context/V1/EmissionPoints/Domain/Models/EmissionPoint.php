<?php

namespace App\Context\V1\EmissionPoints\Domain\Models;

/** Framework-independent emission point aggregate. */
final class EmissionPoint
{
    public function __construct(
        public ?int $id = null,
        public ?int $branch_office_id = null,
        public ?string $name = null,
        public ?string $emission_point = null,
        public bool $status = false,
        public bool $default = false,
        public ?string $created_at = null,
        public ?string $updated_at = null,
        public ?string $deleted_at = null,
    ) {}
}
