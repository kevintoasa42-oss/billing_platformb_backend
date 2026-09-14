<?php

namespace App\Context\V3\Shared\Tenant\Application\UseCases;

use App\Context\V3\Shared\Tenant\Application\Adapters\TenantContextServiceInterface;
use App\Context\V3\Shared\Tenant\Domain\Models\TenantContext;
use App\Context\V3\Shared\Tenant\Domain\Ports\TenantContextConfiguratorInterface;

final readonly class SetTenantContextUseCase implements TenantContextServiceInterface
{
    public function __construct(private TenantContextConfiguratorInterface $configurator) {}

    public function activate(string $tenantId): TenantContext
    {
        $context = new TenantContext($tenantId);
        $this->configurator->activate($context);

        return $context;
    }

    public function clear(): void
    {
        $this->configurator->clear();
    }
}
