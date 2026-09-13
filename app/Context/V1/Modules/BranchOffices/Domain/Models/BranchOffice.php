<?php

namespace App\Context\V1\Modules\BranchOffices\Domain\Models;

/** Framework-independent branch office aggregate. */
final class BranchOffice
{
    public function __construct(
        public ?int    $id = null,
        public ?string $name = null,
        public ?string $code_sri = null,
        public bool    $status = false,
        public ?string $type = null,
        public bool    $default = false,
        public ?string $created_at = null,
        public ?string $updated_at = null,
        public ?string $deleted_at = null,
    )
    {
    }
}
