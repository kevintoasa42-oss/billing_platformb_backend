<?php

namespace App\Context\V1\Modules\EmissionPoints\Infrastructure\Laravel\Providers;

use App\Context\V1\Modules\EmissionPoints\Application\Adapters\EmissionPointSequentialService;
use App\Context\V1\Modules\EmissionPoints\Application\Adapters\EmissionPointSequentialServiceInterface;
use App\Context\V1\Modules\EmissionPoints\Application\Adapters\InvoiceSequentialResolver;
use App\Context\V1\Modules\EmissionPoints\Application\Adapters\InvoiceSequentialResolverInterface;
use App\Context\V1\Modules\EmissionPoints\Domain\Mappers\EmissionPointMapperInterface;
use App\Context\V1\Modules\EmissionPoints\Domain\Ports\NextSequentialGeneratorInterface;
use App\Context\V1\Modules\EmissionPoints\Domain\Repositories\EmissionPointRepositoryInterface;
use App\Context\V1\Modules\EmissionPoints\Infrastructure\Laravel\Eloquent\Repositories\EloquentEmissionPointRepository;
use App\Context\V1\Modules\EmissionPoints\Infrastructure\Mappers\EmissionPointMapper;
use Illuminate\Support\ServiceProvider;

final class EmissionPointServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            EmissionPointMapperInterface::class,
            EmissionPointMapper::class,
        );
        $this->app->bind(
            EmissionPointRepositoryInterface::class,
            EloquentEmissionPointRepository::class,
        );
        $this->app->bind(
            NextSequentialGeneratorInterface::class,
            EloquentEmissionPointRepository::class,
        );
        $this->app->bind(
            EmissionPointSequentialServiceInterface::class,
            EmissionPointSequentialService::class,
        );
        $this->app->bind(
            InvoiceSequentialResolverInterface::class,
            InvoiceSequentialResolver::class,
        );
    }
}
