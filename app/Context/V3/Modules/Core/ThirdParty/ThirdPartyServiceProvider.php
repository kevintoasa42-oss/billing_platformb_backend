<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty;

use App\Context\V3\Modules\Core\ThirdParty\Domain\Repository\ThirdPartyAvailabilityRepositoryInterface;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Repository\ThirdPartyFieldDefinitionRepositoryInterface;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Repository\ThirdPartyFieldRepositoryInterface;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Repository\ThirdPartyQueryRepositoryInterface;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Repository\ThirdPartyRepositoryInterface;
use App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Postgres\ThirdPartyAvailabilityRepository;
use App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Postgres\ThirdPartyFieldDefinitionRepository;
use App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Postgres\ThirdPartyFieldRepository;
use App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Postgres\ThirdPartyRepository;
use Illuminate\Support\ServiceProvider;

class ThirdPartyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ThirdPartyRepositoryInterface::class, ThirdPartyRepository::class);
        $this->app->bind(ThirdPartyQueryRepositoryInterface::class, ThirdPartyRepository::class);
        $this->app->bind(ThirdPartyAvailabilityRepositoryInterface::class, ThirdPartyAvailabilityRepository::class);
        $this->app->bind(ThirdPartyFieldDefinitionRepositoryInterface::class, ThirdPartyFieldDefinitionRepository::class);
        $this->app->bind(ThirdPartyFieldRepositoryInterface::class, ThirdPartyFieldRepository::class);
    }
}
