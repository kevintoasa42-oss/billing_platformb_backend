<?php

namespace App\Context\V3\Shared\Tenant\Infrastructure\Laravel\Providers;

use App\Context\V3\Shared\Tenant\Application\Adapters\TenantConnectionServiceInterface;
use App\Context\V3\Shared\Tenant\Application\Adapters\TenantContextServiceInterface;
use App\Context\V3\Shared\Tenant\Application\UseCases\ConnectTenantUseCase;
use App\Context\V3\Shared\Tenant\Application\UseCases\SetTenantContextUseCase;
use App\Context\V3\Shared\Tenant\Domain\Ports\TenantConnectionConfiguratorInterface;
use App\Context\V3\Shared\Tenant\Domain\Ports\TenantContextConfiguratorInterface;
use App\Context\V3\Shared\Tenant\Domain\Repositories\TenantDatabaseResolverInterface;
use App\Context\V3\Shared\Tenant\Infrastructure\Laravel\Database\LaravelMasterV3TenantContextConfigurator;
use App\Context\V3\Shared\Tenant\Infrastructure\Laravel\Database\LaravelTenantConnectionConfigurator;
use App\Context\V3\Shared\Tenant\Infrastructure\Laravel\Eloquent\Repositories\EloquentTenantDatabaseResolver;
use Illuminate\Support\ServiceProvider;

final class TenantServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TenantDatabaseResolverInterface::class, EloquentTenantDatabaseResolver::class);
        $this->app->bind(TenantConnectionConfiguratorInterface::class, LaravelTenantConnectionConfigurator::class);
        $this->app->bind(TenantConnectionServiceInterface::class, ConnectTenantUseCase::class);
        $this->app->bind(TenantContextConfiguratorInterface::class, LaravelMasterV3TenantContextConfigurator::class);
        $this->app->bind(TenantContextServiceInterface::class, SetTenantContextUseCase::class);
    }
}
