<?php

namespace Tests\Unit\Context\V3\Shared\Tenant\Application;

use App\Context\V3\Shared\Tenant\Application\UseCases\SetTenantContextUseCase;
use App\Context\V3\Shared\Tenant\Domain\Models\TenantContext;
use App\Context\V3\Shared\Tenant\Domain\Ports\TenantContextConfiguratorInterface;
use PHPUnit\Framework\TestCase;

final class SetTenantContextUseCaseTest extends TestCase
{
    public function test_it_activates_and_clears_the_context_for_the_resolved_tenant(): void
    {
        $configurator = new class implements TenantContextConfiguratorInterface
        {
            public ?TenantContext $context = null;

            public bool $cleared = false;

            public function activate(TenantContext $context): void
            {
                $this->context = $context;
            }

            public function clear(): void
            {
                $this->cleared = true;
            }
        };

        $useCase = new SetTenantContextUseCase($configurator);
        $context = $useCase->activate('00000000-0000-4000-8000-000000000001');
        $useCase->clear();

        self::assertSame($context, $configurator->context);
        self::assertTrue($configurator->cleared);
    }
}
