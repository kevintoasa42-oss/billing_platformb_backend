<?php

namespace App\Context\V3\Modules\Authentication\Infrastructure\Laravel\Providers;

use App\Context\V3\Modules\Authentication\Domain\Mappers\AuthenticationMapperInterface;
use App\Context\V3\Modules\Authentication\Domain\Repositories\AuthenticationRepositoryInterface;
use App\Context\V3\Modules\Authentication\Infrastructure\Laravel\Eloquent\Repositories\EloquentAuthenticationRepository;
use App\Context\V3\Modules\Authentication\Infrastructure\Mappers\AuthenticationMapper;
use Illuminate\Support\ServiceProvider;

final class AuthenticationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AuthenticationMapperInterface::class, AuthenticationMapper::class);
        $this->app->bind(AuthenticationRepositoryInterface::class, EloquentAuthenticationRepository::class);
    }
}
