<?php

namespace App\Context\V3\Shared\Tenant\Domain\Ports;

use App\Context\V3\Shared\Tenant\Domain\Models\TenantDatabase;

/** Framework boundary for selecting the runtime tenant database connection. */
interface TenantConnectionConfiguratorInterface
{
    public function configure(TenantDatabase $tenant): void;
}
