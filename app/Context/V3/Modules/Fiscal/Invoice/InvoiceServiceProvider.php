<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Invoice;

use App\Context\V3\Modules\Fiscal\Invoice\Application\Http\Controllers\InvoiceController;
use App\Context\V3\Modules\Fiscal\Invoice\Application\Http\Controllers\InvoiceDraftController;
use App\Context\V3\Modules\Fiscal\Invoice\Application\UseCases\InvoiceDraftUseCase;
use App\Context\V3\Modules\Fiscal\Invoice\Application\UseCases\InvoiceUseCase;
use App\Context\V3\Modules\Fiscal\Invoice\Domain\Repository\InvoiceDraftRepositoryInterface;
use App\Context\V3\Modules\Fiscal\Invoice\Domain\Repository\InvoiceRepositoryInterface;
use App\Context\V3\Modules\Fiscal\Invoice\Infrastructure\Mappers\InvoiceDraftMapper;
use App\Context\V3\Modules\Fiscal\Invoice\Infrastructure\Mappers\InvoiceMapper;
use App\Context\V3\Modules\Fiscal\Invoice\Infrastructure\Postgres\InvoiceDraftRepository;
use App\Context\V3\Modules\Fiscal\Invoice\Infrastructure\Postgres\InvoiceRepository;
use Illuminate\Support\ServiceProvider;

final class InvoiceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(InvoiceMapper::class);
        $this->app->singleton(InvoiceDraftMapper::class);

        $this->app->singleton(InvoiceRepositoryInterface::class, InvoiceRepository::class);
        $this->app->singleton(InvoiceDraftRepositoryInterface::class, InvoiceDraftRepository::class);

        $this->app->singleton(InvoiceUseCase::class);
        $this->app->singleton(InvoiceDraftUseCase::class);

        $this->app->singleton(InvoiceController::class);
        $this->app->singleton(InvoiceDraftController::class);
    }
}
