<?php

namespace App\Context\V3\Shared\Tenant\Infrastructure\Laravel\Database;

use App\Context\V3\Shared\Tenant\Domain\Models\TenantDatabase;
use App\Context\V3\Shared\Tenant\Domain\Ports\TenantConnectionConfiguratorInterface;
use Illuminate\Support\Facades\DB;

final class LaravelTenantConnectionConfigurator implements TenantConnectionConfiguratorInterface
{
    public function configure(TenantDatabase $tenant): void
    {
        config(['database.connections.tenant.database' => $tenant->databaseName]);
        DB::purge('tenant');
        DB::reconnect('tenant');
    }
}
