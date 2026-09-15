<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\Carrier;

use App\Context\V3\Modules\Core\Carrier\Domain\Repository\CarrierAffiliationRepositoryInterface;
use App\Context\V3\Modules\Core\Carrier\Domain\Repository\CarrierCompanyRepositoryInterface;
use App\Context\V3\Modules\Core\Carrier\Domain\Repository\CarrierEmissionPointRepositoryInterface;
use App\Context\V3\Modules\Core\Carrier\Domain\Repository\CarrierEstablishmentRepositoryInterface;
use App\Context\V3\Modules\Core\Carrier\Domain\Repository\CarrierProfileRepositoryInterface;
use App\Context\V3\Modules\Core\Carrier\Infrastructure\Postgres\CarrierAffiliationRepository;
use App\Context\V3\Modules\Core\Carrier\Infrastructure\Postgres\CarrierCompanyRepository;
use App\Context\V3\Modules\Core\Carrier\Infrastructure\Postgres\CarrierEmissionPointRepository;
use App\Context\V3\Modules\Core\Carrier\Infrastructure\Postgres\CarrierEstablishmentRepository;
use App\Context\V3\Modules\Core\Carrier\Infrastructure\Postgres\CarrierProfileRepository;
use Illuminate\Support\ServiceProvider;

class CarrierServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CarrierAffiliationRepositoryInterface::class, CarrierAffiliationRepository::class);
        $this->app->bind(CarrierCompanyRepositoryInterface::class, CarrierCompanyRepository::class);
        $this->app->bind(CarrierEstablishmentRepositoryInterface::class, CarrierEstablishmentRepository::class);
        $this->app->bind(CarrierEmissionPointRepositoryInterface::class, CarrierEmissionPointRepository::class);
        $this->app->bind(CarrierProfileRepositoryInterface::class, CarrierProfileRepository::class);
    }
}
