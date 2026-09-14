<?php

namespace App\Context\V3\Modules\Core\Establishment;

use App\Context\V3\Modules\Core\Establishment\Domain\Repository\EmissionPointRepositoryInterface;
use App\Context\V3\Modules\Core\Establishment\Domain\Repository\EstablishmentRepositoryInterface;
use App\Context\V3\Modules\Core\Establishment\Infrastructure\Postgres\EmissionPointRepository;
use App\Context\V3\Modules\Core\Establishment\Infrastructure\Postgres\EstablishmentRepository;
use Illuminate\Support\ServiceProvider;

class EstablishmentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(EstablishmentRepositoryInterface::class, EstablishmentRepository::class);
        $this->app->bind(EmissionPointRepositoryInterface::class, EmissionPointRepository::class);
    }
}
