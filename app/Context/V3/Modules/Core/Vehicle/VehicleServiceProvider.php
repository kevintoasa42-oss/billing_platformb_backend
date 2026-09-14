<?php

namespace App\Context\V3\Modules\Core\Vehicle;

use App\Context\V3\Modules\Core\Vehicle\Domain\Repository\VehicleRepositoryInterface;
use App\Context\V3\Modules\Core\Vehicle\Infrastructure\Postgres\VehicleRepository;
use Illuminate\Support\ServiceProvider;

class VehicleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(VehicleRepositoryInterface::class, VehicleRepository::class);
    }
}
