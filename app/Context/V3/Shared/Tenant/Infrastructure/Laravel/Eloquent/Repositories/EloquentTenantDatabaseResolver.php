<?php

namespace App\Context\V3\Shared\Tenant\Infrastructure\Laravel\Eloquent\Repositories;

use App\Context\V3\Shared\Tenant\Domain\Models\TenantDatabase;
use App\Context\V3\Shared\Tenant\Domain\Repositories\TenantDatabaseResolverInterface;
use App\Context\V3\Shared\Tenant\Infrastructure\Laravel\Eloquent\Models\TenantEnterpriseModel;

final class EloquentTenantDatabaseResolver implements TenantDatabaseResolverInterface
{
    public function findByEnterpriseId(int $enterpriseId): ?TenantDatabase
    {
        $enterprise = TenantEnterpriseModel::query()->find($enterpriseId);
        $databaseName = trim((string) ($enterprise?->getAttribute('db_name') ?? ''));

        return $enterprise && $databaseName !== ''
            ? new TenantDatabase((int) $enterprise->getKey(), $databaseName)
            : null;
    }
}
