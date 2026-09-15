<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Worker;

use App\Context\V3\Modules\Fiscal\Worker\Domain\Ports\FiscalPipelineInterface;
use App\Context\V3\Modules\Fiscal\Worker\Domain\Repository\ProcessingJobRepositoryInterface;
use App\Context\V3\Modules\Fiscal\Worker\Infrastructure\Laravel\Pipelines\CelcerFiscalPipeline;
use App\Context\V3\Modules\Fiscal\Worker\Infrastructure\Laravel\Pipelines\LabFiscalPipeline;
use App\Context\V3\Modules\Fiscal\Worker\Infrastructure\Mappers\ProcessingJobMapper;
use App\Context\V3\Modules\Fiscal\Worker\Infrastructure\Postgres\FiscalWorkerRepository;
use App\Context\V3\Modules\Fiscal\Worker\Infrastructure\Postgres\ProcessingJobRepository;
use Illuminate\Support\ServiceProvider;

final class WorkerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProcessingJobRepositoryInterface::class, ProcessingJobRepository::class);

        $this->app->bind(ProcessingJobMapper::class);

        // Select the pipeline based on SRI mode: mock -> Lab, celcer/sri -> Celcer
        $this->app->bind(FiscalPipelineInterface::class, function ($app) {
            $mode = strtolower((string) config('services.sri.mode', 'mock'));

            if ($mode === 'mock') {
                return $app->make(LabFiscalPipeline::class);
            }

            return $app->make(CelcerFiscalPipeline::class);
        });

        $this->app->bind(FiscalWorkerRepository::class, function ($app) {
            return new FiscalWorkerRepository(
                $app->make(ProcessingJobRepository::class),
                $app->make(FiscalPipelineInterface::class),
                $app->make(\App\Context\V3\Modules\Fiscal\Invoice\Infrastructure\Mappers\InvoiceMapper::class),
            );
        });
    }
}
