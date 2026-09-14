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
            \App\Context\V1\Modules\Clients\Enterprise\Domain\Mappers\EnterpriseMapperInterface::class,
            \App\Context\V1\Modules\Clients\Enterprise\Infrastructure\Eloquent\Mappers\EnterpriseMapper::class
        );
        $this->app->bind(
            \App\Context\V1\Modules\Clients\Enterprise\Domain\Mappers\UserMapperInterface::class,
            \App\Context\V1\Modules\Clients\Enterprise\Infrastructure\Eloquent\Mappers\UserMapper::class
        );
        $this->app->bind(
            \App\Context\V1\Modules\Clients\Enterprise\Domain\Mappers\RoleMapperInterface::class,
            \App\Context\V1\Modules\Clients\Enterprise\Infrastructure\Eloquent\Mappers\RoleMapper::class
        );

        // Menu
        $this->app->bind(
            \App\Context\V1\Modules\Menu\Domain\Mappers\MenuMapperInterface::class,
            \App\Context\V1\Modules\Menu\Infrastructure\Eloquent\Mappers\MenuMapper::class
        );

        // === Repositories (interfaces -> implementaciones Eloquent) ===

        // Enterprise
        $this->app->bind(
            \App\Context\V1\Modules\Clients\Enterprise\Domain\Repositories\EnterpriseRepositoryInterface::class,
            \App\Context\V1\Modules\Clients\Enterprise\Infrastructure\Eloquent\Repositories\EloquentEnterpriseRepository::class
        );
        $this->app->bind(
            \App\Context\V1\Modules\Clients\Enterprise\Domain\Repositories\UserRepositoryInterface::class,
            \App\Context\V1\Modules\Clients\Enterprise\Infrastructure\Eloquent\Repositories\EloquentUserRepository::class
        );
        $this->app->bind(
            \App\Context\V1\Modules\Clients\Enterprise\Domain\Repositories\RoleRepositoryInterface::class,
            \App\Context\V1\Modules\Clients\Enterprise\Infrastructure\Eloquent\Repositories\EloquentRoleRepository::class
        );

        // Menu
        $this->app->bind(
            \App\Context\V1\Modules\Menu\Domain\Repositories\MenuRepositoryInterface::class,
            \App\Context\V1\Modules\Menu\Infrastructure\Eloquent\Repositories\EloquentMenuRepository::class
        );

        // Product
        $this->app->bind(
            \App\Context\V1\Modules\Product\Domain\Repositories\ProductRepositoryInterface::class,
            \App\Context\V1\Modules\Product\Infrastructure\Eloquent\Repositories\EloquentProductRepository::class
        );

        // Carrier
        $this->app->bind(
            \App\Context\V1\Modules\Carrier\Domain\Repositories\CarrierRepositoryInterface::class,
            \App\Context\V1\Modules\Carrier\Infrastructure\Eloquent\Repositories\EloquentCarrierRepository::class
        );

        // Signature
        $this->app->bind(
            \App\Context\V1\Modules\Signature\Domain\Repositories\SignatureRepositoryInterface::class,
            \App\Context\V1\Modules\Signature\Infrastructure\Eloquent\Repositories\EloquentSignatureRepository::class
        );

        // Enterprise Signature
        $this->app->bind(
            \App\Context\V1\Modules\Signature\Domain\Repositories\EnterpriseSignatureRepositoryInterface::class,
            \App\Context\V1\Modules\Signature\Infrastructure\Eloquent\Repositories\EloquentEnterpriseSignatureRepository::class
        );


        // Invoice
        $this->app->bind(
            \App\Context\V1\Modules\Invoice\Domain\Repositories\InvoiceRepositoryInterface::class,
            \App\Context\V1\Modules\Invoice\Infrastructure\Eloquent\Repositories\EloquentInvoiceRepository::class
        );

        // SRI Catalog (central DB, used by Invoice domain service)
        $this->app->bind(
            \App\Context\V1\Modules\Invoice\Domain\Repositories\SriCatalogRepositoryInterface::class,
            \App\Context\V1\Modules\Invoice\Infrastructure\Eloquent\Repositories\EloquentSriCatalogRepository::class
        );

        // Signature config (resolves environment/emission_type from signature)
        $this->app->bind(
            \App\Context\V1\Modules\Invoice\Domain\Repositories\SignatureConfigRepositoryInterface::class,
            \App\Context\V1\Modules\Invoice\Infrastructure\Eloquent\Repositories\EloquentSignatureConfigRepository::class
        );

        // Shared services
        $this->app->singleton(
            \App\Context\V1\Shared\AccessKey\Domain\Services\AccessKeyGenerator::class
        );

        // XmlGeneration - Invoice XML builder (needs provider RUC from config)
        $this->app->singleton(
            \App\Context\V1\Modules\XmlGeneration\Domain\Services\InvoiceXmlBuilder::class,
            fn ($app) => new \App\Context\V1\Modules\XmlGeneration\Domain\Services\InvoiceXmlBuilder(
                config('sri.provider_ruc'),
            )
        );

        // SriAuthorization - SRI SOAP communication services
        $this->app->singleton(\App\Context\V1\Modules\SriAuthorization\Domain\Services\SriReceptionService::class);
        $this->app->singleton(\App\Context\V1\Modules\SriAuthorization\Domain\Services\SriAuthorizationService::class);

        // SriAuthorization - log repository
        $this->app->bind(
            \App\Context\V1\Modules\SriAuthorization\Domain\Repositories\InvoiceSriLogRepositoryInterface::class,
            \App\Context\V1\Modules\SriAuthorization\Infrastructure\Eloquent\Repositories\EloquentInvoiceSriLogRepository::class
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
