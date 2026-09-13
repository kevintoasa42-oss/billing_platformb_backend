<?php

namespace App\Context\V1\Modules\Clients\Infrastructure\Laravel\Providers;

use App\Context\V1\Modules\Clients\Domain\Mappers\ClientMapperInterface;
use App\Context\V1\Modules\Clients\Domain\Repositories\ClientRepositoryInterface;
use App\Context\V1\Modules\Clients\Infrastructure\Laravel\Eloquent\Repositories\EloquentClientRepository;
use App\Context\V1\Modules\Clients\Infrastructure\Mappers\ClientMapper;
use Illuminate\Support\ServiceProvider;

final class ClientServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ClientMapperInterface::class,
            ClientMapper::class,
        );
        $this->app->bind(
            ClientRepositoryInterface::class,
            EloquentClientRepository::class,
        );
    }
}
