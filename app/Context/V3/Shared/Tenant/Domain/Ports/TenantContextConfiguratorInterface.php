<?php

namespace App\Context\V3\Shared\Tenant\Domain\Ports;

use App\Context\V3\Shared\Tenant\Domain\Models\TenantContext;

/** Port used to apply a V3 tenant context to the underlying data store. */
interface TenantContextConfiguratorInterface
{
    public function activate(TenantContext $context): void;

    public function clear(): void;
}
