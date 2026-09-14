<?php

namespace App\Context\V3\Modules\Core\SriIva;

use App\Context\V3\Modules\Core\SriIva\Domain\Repository\SriIvaPercentageRepositoryInterface;
use App\Context\V3\Modules\Core\SriIva\Domain\Repository\SriIvaTypeRepositoryInterface;
use App\Context\V3\Modules\Core\SriIva\Infrastructure\Postgres\SriIvaPercentageRepository;
use App\Context\V3\Modules\Core\SriIva\Infrastructure\Postgres\SriIvaTypeRepository;
use Illuminate\Support\ServiceProvider;

class SriIvaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SriIvaTypeRepositoryInterface::class, SriIvaTypeRepository::class);
        $this->app->bind(SriIvaPercentageRepositoryInterface::class, SriIvaPercentageRepository::class);
    }
}
