<?php

namespace App\Context\V3\Shared\Tenant\Domain\Repositories;

use App\Context\V3\Shared\Tenant\Domain\Models\TenantDatabase;

interface TenantDatabaseResolverInterface
{
    public function findByEnterpriseId(int $enterpriseId): ?TenantDatabase;
}
