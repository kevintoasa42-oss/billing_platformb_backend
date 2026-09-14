<?php

namespace App\Context\V3\Shared\Tenant\Application\Adapters;

use App\Context\V3\Shared\Tenant\Domain\Models\TenantDatabase;

/** Stable application contract for code that needs to select a tenant. */
interface TenantConnectionServiceInterface
{
    public function connectForEnterprise(int $enterpriseId): TenantDatabase;
}
