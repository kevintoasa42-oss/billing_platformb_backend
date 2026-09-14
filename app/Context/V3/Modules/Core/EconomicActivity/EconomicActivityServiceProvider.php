<?php

namespace App\Context\V3\Modules\Core\EconomicActivity;

use App\Context\V3\Modules\Core\EconomicActivity\Domain\Repository\EconomicActivityRepositoryInterface;
use App\Context\V3\Modules\Core\EconomicActivity\Infrastructure\Postgres\EconomicActivityRepository;
use Illuminate\Support\ServiceProvider;

class EconomicActivityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(EconomicActivityRepositoryInterface::class, EconomicActivityRepository::class);
    }
}
