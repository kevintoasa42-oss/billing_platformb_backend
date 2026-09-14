<?php

namespace App\Context\V3\Shared\Tenant\Domain\Exceptions;

use RuntimeException;

final class TenantDatabaseNotFoundException extends RuntimeException
{
    public function __construct(int $enterpriseId)
    {
        parent::__construct("Enterprise [{$enterpriseId}] does not have a configured tenant database.");
    }
}
