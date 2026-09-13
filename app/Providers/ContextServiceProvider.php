<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ContextServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // === Mappers (interfaces -> implementaciones concretas) ===

        // Enterprise
        $this->app->bind(
            \App\Context\V1\Enterprise\Domain\Mappers\EnterpriseMapperInterface::class,
            \App\Context\V1\Enterprise\Infrastructure\Eloquent\Mappers\EnterpriseMapper::class
        );
        $this->app->bind(
            \App\Context\V1\Enterprise\Domain\Mappers\UserMapperInterface::class,
            \App\Context\V1\Enterprise\Infrastructure\Eloquent\Mappers\UserMapper::class
        );
        $this->app->bind(
            \App\Context\V1\Enterprise\Domain\Mappers\RoleMapperInterface::class,
            \App\Context\V1\Enterprise\Infrastructure\Eloquent\Mappers\RoleMapper::class
        );

        // Menu
        $this->app->bind(
            \App\Context\V1\Menu\Domain\Mappers\MenuMapperInterface::class,
            \App\Context\V1\Menu\Infrastructure\Eloquent\Mappers\MenuMapper::class
        );

        // === Repositories (interfaces -> implementaciones Eloquent) ===

        // Enterprise
        $this->app->bind(
            \App\Context\V1\Enterprise\Domain\Repositories\EnterpriseRepositoryInterface::class,
            \App\Context\V1\Enterprise\Infrastructure\Eloquent\Repositories\EloquentEnterpriseRepository::class
        );
        $this->app->bind(
            \App\Context\V1\Enterprise\Domain\Repositories\UserRepositoryInterface::class,
            \App\Context\V1\Enterprise\Infrastructure\Eloquent\Repositories\EloquentUserRepository::class
        );
        $this->app->bind(
            \App\Context\V1\Enterprise\Domain\Repositories\RoleRepositoryInterface::class,
            \App\Context\V1\Enterprise\Infrastructure\Eloquent\Repositories\EloquentRoleRepository::class
        );

        // Menu
        $this->app->bind(
            \App\Context\V1\Menu\Domain\Repositories\MenuRepositoryInterface::class,
            \App\Context\V1\Menu\Infrastructure\Eloquent\Repositories\EloquentMenuRepository::class
        );

        // Product
        $this->app->bind(
            \App\Context\V1\Product\Domain\Repositories\ProductRepositoryInterface::class,
            \App\Context\V1\Product\Infrastructure\Eloquent\Repositories\EloquentProductRepository::class
        );

        // Carrier
        $this->app->bind(
            \App\Context\V1\Carrier\Domain\Repositories\CarrierRepositoryInterface::class,
            \App\Context\V1\Carrier\Infrastructure\Eloquent\Repositories\EloquentCarrierRepository::class
        );

        // Signature
        $this->app->bind(
            \App\Context\V1\Signature\Domain\Repositories\SignatureRepositoryInterface::class,
            \App\Context\V1\Signature\Infrastructure\Eloquent\Repositories\EloquentSignatureRepository::class
        );

        // Enterprise Signature
        $this->app->bind(
            \App\Context\V1\Signature\Domain\Repositories\EnterpriseSignatureRepositoryInterface::class,
            \App\Context\V1\Signature\Infrastructure\Eloquent\Repositories\EloquentEnterpriseSignatureRepository::class
        );

        // Invoice
        $this->app->bind(
            \App\Context\V1\Invoice\Domain\Repositories\InvoiceRepositoryInterface::class,
            \App\Context\V1\Invoice\Infrastructure\Eloquent\Repositories\EloquentInvoiceRepository::class
        );

        // SRI Catalog (central DB, used by Invoice domain service)
        $this->app->bind(
            \App\Context\V1\Invoice\Domain\Repositories\SriCatalogRepositoryInterface::class,
            \App\Context\V1\Invoice\Infrastructure\Eloquent\Repositories\EloquentSriCatalogRepository::class
        );

        // Shared services
        $this->app->singleton(
            \App\Context\V1\Shared\Domain\Services\AccessKeyGenerator::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
