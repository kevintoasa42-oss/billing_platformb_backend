<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty;

use App\Context\V3\Modules\Core\ThirdParty\Domain\Mappers\ThirdPartyActivityMapperInterface;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Mappers\ThirdPartyMapperInterface;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Mappers\ThirdPartyRoleMapperInterface;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Repository\ThirdPartyFieldRepositoryInterface;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Repository\ThirdPartyRepositoryInterface;
use App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Mappers\ThirdPartyActivityMapper;
use App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Mappers\ThirdPartyMapper;
use App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Mappers\ThirdPartyRoleMapper;
use App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Postgres\ThirdPartyFieldRepository;
use App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Postgres\ThirdPartyRepository;
use Illuminate\Support\ServiceProvider;

class ThirdPartyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ThirdPartyRepositoryInterface::class, ThirdPartyRepository::class);
        $this->app->bind(ThirdPartyFieldRepositoryInterface::class, ThirdPartyFieldRepository::class);
        $this->app->bind(ThirdPartyMapperInterface::class, ThirdPartyMapper::class);
        $this->app->bind(ThirdPartyRoleMapperInterface::class, ThirdPartyRoleMapper::class);
        $this->app->bind(ThirdPartyActivityMapperInterface::class, ThirdPartyActivityMapper::class);
    }
}
