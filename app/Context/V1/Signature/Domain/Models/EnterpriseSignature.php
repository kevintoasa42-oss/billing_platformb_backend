<?php

namespace App\Context\V1\Signature\Domain\Models;

class EnterpriseSignature
{
    public function __construct(
        public ?int $id = null,
        public ?int $enterprise_id = null,
        public ?string $file_name = null,
        public ?string $file_path = null,
        public ?string $password = null,
        public ?string $expires_at = null,
        public ?string $environment = null,
        public bool $emission_type = true,
        public bool $status = true,
    ) {}
}
