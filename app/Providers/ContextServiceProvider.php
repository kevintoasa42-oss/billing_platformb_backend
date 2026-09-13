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
            \App\Context\Enterprise\Domain\Mappers\EnterpriseMapperInterface::class,
            \App\Context\Enterprise\Infrastructure\Eloquent\Mappers\EnterpriseMapper::class
        );
        $this->app->bind(
            \App\Context\Enterprise\Domain\Mappers\UserMapperInterface::class,
            \App\Context\Enterprise\Infrastructure\Eloquent\Mappers\UserMapper::class
        );
        $this->app->bind(
            \App\Context\Enterprise\Domain\Mappers\RoleMapperInterface::class,
            \App\Context\Enterprise\Infrastructure\Eloquent\Mappers\RoleMapper::class
        );

        // Menu
        $this->app->bind(
            \App\Context\Menu\Domain\Mappers\MenuMapperInterface::class,
            \App\Context\Menu\Infrastructure\Eloquent\Mappers\MenuMapper::class
        );

        // === Repositories (interfaces -> implementaciones Eloquent) ===

        // Enterprise
        $this->app->bind(
            \App\Context\Enterprise\Domain\Repositories\EnterpriseRepositoryInterface::class,
            \App\Context\Enterprise\Infrastructure\Eloquent\Repositories\EloquentEnterpriseRepository::class
        );
        $this->app->bind(
            \App\Context\Enterprise\Domain\Repositories\UserRepositoryInterface::class,
            \App\Context\Enterprise\Infrastructure\Eloquent\Repositories\EloquentUserRepository::class
        );
        $this->app->bind(
            \App\Context\Enterprise\Domain\Repositories\RoleRepositoryInterface::class,
            \App\Context\Enterprise\Infrastructure\Eloquent\Repositories\EloquentRoleRepository::class
        );

        // Menu
        $this->app->bind(
            \App\Context\Menu\Domain\Repositories\MenuRepositoryInterface::class,
            \App\Context\Menu\Infrastructure\Eloquent\Repositories\EloquentMenuRepository::class
        );

        // Product
        $this->app->bind(
            \App\Context\Product\Domain\Mappers\ProductMapperInterface::class,
            \App\Context\Product\Infrastructure\Eloquent\Mappers\EloquentProductMapper::class
        );
        $this->app->bind(
            \App\Context\Product\Domain\Repositories\ProductRepositoryInterface::class,
            \App\Context\Product\Infrastructure\Eloquent\Repositories\EloquentProductRepository::class
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
