<?php

namespace App\Context\V3\Shared\Tenant\Application\UseCases;

use App\Context\V3\Shared\Tenant\Application\Adapters\TenantConnectionServiceInterface;
use App\Context\V3\Shared\Tenant\Domain\Exceptions\TenantDatabaseNotFoundException;
use App\Context\V3\Shared\Tenant\Domain\Models\TenantDatabase;
use App\Context\V3\Shared\Tenant\Domain\Ports\TenantConnectionConfiguratorInterface;
use App\Context\V3\Shared\Tenant\Domain\Repositories\TenantDatabaseResolverInterface;

final readonly class ConnectTenantUseCase implements TenantConnectionServiceInterface
{
    public function __construct(
        private TenantDatabaseResolverInterface $resolver,
        private TenantConnectionConfiguratorInterface $configurator,
    ) {}

    public function connectForEnterprise(int $enterpriseId): TenantDatabase
    {
        $tenant = $this->resolver->findByEnterpriseId($enterpriseId);
        if (! $tenant) {
            throw new TenantDatabaseNotFoundException($enterpriseId);
        }

        $this->configurator->configure($tenant);

        return $tenant;
    }
}
