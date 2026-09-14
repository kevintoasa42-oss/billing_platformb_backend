<?php

namespace App\Context\V1\Modules\SriVoucherTypes\Infrastructure\Laravel\Providers;

use App\Context\V1\Modules\SriVoucherTypes\Application\Adapters\SriVoucherTypeCatalogInterface;
use App\Context\V1\Modules\SriVoucherTypes\Application\Adapters\SriVoucherTypeCatalogService;
use App\Context\V1\Modules\SriVoucherTypes\Domain\Mappers\SriVoucherTypeMapperInterface;
use App\Context\V1\Modules\SriVoucherTypes\Domain\Repositories\SriVoucherTypeRepositoryInterface;
use App\Context\V1\Modules\SriVoucherTypes\Infrastructure\Laravel\Eloquent\Repositories\EloquentSriVoucherTypeRepository;
use App\Context\V1\Modules\SriVoucherTypes\Infrastructure\Mappers\SriVoucherTypeMapper;
use Illuminate\Support\ServiceProvider;

final class SriVoucherTypeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SriVoucherTypeMapperInterface::class, SriVoucherTypeMapper::class);
        $this->app->bind(SriVoucherTypeRepositoryInterface::class, EloquentSriVoucherTypeRepository::class);
        $this->app->bind(SriVoucherTypeCatalogInterface::class, SriVoucherTypeCatalogService::class);
    }
}
