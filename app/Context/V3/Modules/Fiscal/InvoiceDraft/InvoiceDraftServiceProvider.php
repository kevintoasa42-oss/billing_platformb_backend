<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\InvoiceDraft;

use App\Context\V3\Modules\Fiscal\InvoiceDraft\Domain\Repository\InvoiceDraftRepositoryInterface;
use App\Context\V3\Modules\Fiscal\InvoiceDraft\Infrastructure\Postgres\EloquentInvoiceDraftRepository;
use Illuminate\Support\ServiceProvider;

final class InvoiceDraftServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(InvoiceDraftRepositoryInterface::class, EloquentInvoiceDraftRepository::class);
    }
}
