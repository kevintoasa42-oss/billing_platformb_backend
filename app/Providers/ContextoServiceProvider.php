<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ContextoServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // === Mappers (interfaces -> implementaciones concretas) ===

        // Enterprise
        $this->app->bind(
            \App\Contexto\Enterprise\Dominio\Mappers\EmpresaMapperInterface::class,
            \App\Contexto\Enterprise\Infraestructura\Eloquent\Mappers\EmpresaMapper::class
        );
        $this->app->bind(
            \App\Contexto\Enterprise\Dominio\Mappers\UsuarioMapperInterface::class,
            \App\Contexto\Enterprise\Infraestructura\Eloquent\Mappers\UsuarioMapper::class
        );
        $this->app->bind(
            \App\Contexto\Enterprise\Dominio\Mappers\RolMapperInterface::class,
            \App\Contexto\Enterprise\Infraestructura\Eloquent\Mappers\RolMapper::class
        );

        // Menu
        $this->app->bind(
            \App\Contexto\Menu\Dominio\Mappers\MenuMapperInterface::class,
            \App\Contexto\Menu\Infraestructura\Eloquent\Mappers\MenuMapper::class
        );

        // === Repositorios (interfaces -> implementaciones Eloquent) ===

        // Enterprise
        $this->app->bind(
            \App\Contexto\Enterprise\Dominio\Repositorios\EmpresaRepositoryInterface::class,
            \App\Contexto\Enterprise\Infraestructura\Eloquent\Repositorios\EloquentEmpresaRepository::class
        );
        $this->app->bind(
            \App\Contexto\Enterprise\Dominio\Repositorios\UsuarioRepositoryInterface::class,
            \App\Contexto\Enterprise\Infraestructura\Eloquent\Repositorios\EloquentUsuarioRepository::class
        );
        $this->app->bind(
            \App\Contexto\Enterprise\Dominio\Repositorios\RolRepositoryInterface::class,
            \App\Contexto\Enterprise\Infraestructura\Eloquent\Repositorios\EloquentRolRepository::class
        );

        // Menu
        $this->app->bind(
            \App\Contexto\Menu\Dominio\Repositorios\MenuRepositoryInterface::class,
            \App\Contexto\Menu\Infraestructura\Eloquent\Repositorios\EloquentMenuRepository::class
        );

        // Product
        $this->app->bind(
            \App\Contexto\Product\Dominio\Mappers\ProductoMapperInterface::class,
            \App\Contexto\Product\Infraestructura\Eloquent\Mappers\EloquentProductoMapper::class
        );
        $this->app->bind(
            \App\Contexto\Product\Dominio\Repositorios\ProductoRepositoryInterface::class,
            \App\Contexto\Product\Infraestructura\Eloquent\Repositories\EloquentProductoRepository::class
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
