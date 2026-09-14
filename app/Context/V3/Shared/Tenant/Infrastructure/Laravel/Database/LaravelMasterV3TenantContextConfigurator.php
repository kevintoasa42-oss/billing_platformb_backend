<?php

namespace App\Context\V3\Shared\Tenant\Infrastructure\Laravel\Database;

use App\Context\V3\Shared\Tenant\Domain\Models\TenantContext;
use App\Context\V3\Shared\Tenant\Domain\Ports\TenantContextConfiguratorInterface;
use Illuminate\Support\Facades\DB;

/** Applies PostgreSQL's app.tenant_id setting for master_v3 row-level security. */
final class LaravelMasterV3TenantContextConfigurator implements TenantContextConfiguratorInterface
{
    public function activate(TenantContext $context): void
    {
        DB::connection('master_v3')->select(
            "SELECT set_config('app.tenant_id', ?, false)",
            [$context->tenantId],
        );
    }

    public function clear(): void
    {
        DB::connection('master_v3')->select("SELECT set_config('app.tenant_id', '', false)");
    }
}
