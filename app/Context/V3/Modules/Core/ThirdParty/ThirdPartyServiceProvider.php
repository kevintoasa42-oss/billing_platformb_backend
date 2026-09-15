<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty;

use App\Context\V3\Modules\Core\ThirdParty\Domain\Repository\ThirdPartyAvailabilityRepositoryInterface;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Repository\ThirdPartyFieldDefinitionRepositoryInterface;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Repository\ThirdPartyFieldValueRepositoryInterface;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Repository\ThirdPartyRepositoryInterface;
use App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Postgres\EloquentThirdPartyAvailabilityRepository;
use App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Postgres\EloquentThirdPartyFieldDefinitionRepository;
use App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Postgres\EloquentThirdPartyFieldValueRepository;
use App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Postgres\EloquentThirdPartyRepository;
use Illuminate\Support\ServiceProvider;

final class ThirdPartyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ThirdPartyAvailabilityRepositoryInterface::class, EloquentThirdPartyAvailabilityRepository::class);
        $this->app->bind(ThirdPartyFieldDefinitionRepositoryInterface::class, EloquentThirdPartyFieldDefinitionRepository::class);
        $this->app->bind(ThirdPartyFieldValueRepositoryInterface::class, EloquentThirdPartyFieldValueRepository::class);
        $this->app->bind(ThirdPartyRepositoryInterface::class, EloquentThirdPartyRepository::class);
    }
}
