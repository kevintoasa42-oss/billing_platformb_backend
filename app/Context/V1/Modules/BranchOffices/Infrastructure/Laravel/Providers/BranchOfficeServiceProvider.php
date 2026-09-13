<?php

namespace App\Context\V1\Modules\BranchOffices\Infrastructure\Laravel\Providers;

use App\Context\V1\Modules\BranchOffices\Domain\Mappers\BranchOfficeMapperInterface;
use App\Context\V1\Modules\BranchOffices\Domain\Repositories\BranchOfficeRepositoryInterface;
use App\Context\V1\Modules\BranchOffices\Infrastructure\Laravel\Eloquent\Repositories\EloquentBranchOfficeRepository;
use App\Context\V1\Modules\BranchOffices\Infrastructure\Mappers\BranchOfficeMapper;
use Illuminate\Support\ServiceProvider;

final class BranchOfficeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            BranchOfficeMapperInterface::class,
            BranchOfficeMapper::class,
        );
        $this->app->bind(
            BranchOfficeRepositoryInterface::class,
            EloquentBranchOfficeRepository::class,
        );
    }
}
