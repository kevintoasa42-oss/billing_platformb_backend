<?php

namespace App\Context\V3\Modules\Core\Company;

use App\Context\V3\Modules\Core\Company\Domain\Repository\CompanyRepositoryInterface;
use App\Context\V3\Modules\Core\Company\Infrastructure\Postgres\CompanyRepository;
use Illuminate\Support\ServiceProvider;

class CompanyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CompanyRepositoryInterface::class, CompanyRepository::class);
    }
}
