<?php

namespace App\Context\V3\Modules\Platform;

use App\Context\V3\Modules\Platform\Domain\Repositories\PlatformAdministrationRepositoryInterface;
use App\Context\V3\Modules\Platform\Infrastructure\Laravel\Eloquent\Repositories\EloquentPlatformAdministrationRepository;
use Illuminate\Support\ServiceProvider;

final class PlatformServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PlatformAdministrationRepositoryInterface::class, EloquentPlatformAdministrationRepository::class);
    }
}
