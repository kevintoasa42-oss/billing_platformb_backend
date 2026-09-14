<?php

namespace App\Context\V3\Shared\Tenant\Domain\Models;

/** A landlord enterprise and the database assigned to its tenant. */
final class TenantDatabase
{
    public function __construct(
        public readonly int $enterpriseId,
        public readonly string $databaseName,
    ) {}
}
