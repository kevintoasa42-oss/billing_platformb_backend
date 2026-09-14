<?php

namespace App\Context\V3\Shared\Tenant\Application\Adapters;

use App\Context\V3\Shared\Tenant\Domain\Models\TenantContext;

interface TenantContextServiceInterface
{
    public function activate(string $tenantId): TenantContext;

    public function clear(): void;
}
