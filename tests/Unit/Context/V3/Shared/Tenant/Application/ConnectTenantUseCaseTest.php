<?php

namespace Tests\Unit\Context\V3\Shared\Tenant\Application;

use App\Context\V3\Shared\Tenant\Application\UseCases\ConnectTenantUseCase;
use App\Context\V3\Shared\Tenant\Domain\Exceptions\TenantDatabaseNotFoundException;
use App\Context\V3\Shared\Tenant\Domain\Models\TenantDatabase;
use App\Context\V3\Shared\Tenant\Domain\Ports\TenantConnectionConfiguratorInterface;
use App\Context\V3\Shared\Tenant\Domain\Repositories\TenantDatabaseResolverInterface;
use PHPUnit\Framework\TestCase;

final class ConnectTenantUseCaseTest extends TestCase
{
    public function test_it_resolves_and_configures_the_tenant_database_for_an_enterprise(): void
    {
        $resolver = new class implements TenantDatabaseResolverInterface
        {
            public function findByEnterpriseId(int $enterpriseId): ?TenantDatabase
            {
                return $enterpriseId === 7 ? new TenantDatabase(7, '1791234567001') : null;
            }
        };
        $configurator = new class implements TenantConnectionConfiguratorInterface
        {
            public ?TenantDatabase $tenant = null;

            public function configure(TenantDatabase $tenant): void
            {
                $this->tenant = $tenant;
            }
        };

        $useCase = new ConnectTenantUseCase($resolver, $configurator);
        $tenant = $useCase->connectForEnterprise(7);

        self::assertSame('1791234567001', $tenant->databaseName);
        self::assertSame(7, $configurator->tenant?->enterpriseId);
    }

    public function test_it_rejects_an_enterprise_without_a_tenant_database(): void
    {
        $this->expectException(TenantDatabaseNotFoundException::class);

        $resolver = new class implements TenantDatabaseResolverInterface
        {
            public function findByEnterpriseId(int $enterpriseId): ?TenantDatabase
            {
                return null;
            }
        };
        $configurator = new class implements TenantConnectionConfiguratorInterface
        {
            public function configure(TenantDatabase $tenant): void {}
        };

        (new ConnectTenantUseCase($resolver, $configurator))->connectForEnterprise(999);
    }
}
