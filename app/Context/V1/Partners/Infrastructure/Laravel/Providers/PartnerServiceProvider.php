<?php

namespace App\Context\V1\Partners\Infrastructure\Laravel\Providers;

use App\Context\V1\Partners\Domain\Mappers\PartnerMapperInterface;
use App\Context\V1\Partners\Domain\Repositories\PartnerRepositoryInterface;
use App\Context\V1\Partners\Infrastructure\Laravel\Eloquent\Repositories\EloquentPartnerRepository;
use App\Context\V1\Partners\Infrastructure\Mappers\PartnerMapper;
use Illuminate\Support\ServiceProvider;

final class PartnerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PartnerMapperInterface::class, PartnerMapper::class);
        $this->app->bind(PartnerRepositoryInterface::class, EloquentPartnerRepository::class);
    }
}
