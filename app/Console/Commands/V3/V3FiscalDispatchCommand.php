<?php

declare(strict_types=1);

namespace App\Console\Commands\V3;

use App\Context\V3\Modules\Fiscal\Worker\Application\UseCases\ProcessFiscalJobsUseCase;
use Illuminate\Console\Command;

/**
 * One-shot dispatch command. Drains all pending jobs and exits.
 */
final class V3FiscalDispatchCommand extends Command
{
    protected $signature = 'v3:fiscal:dispatch-pending
                            {--limit=100 : Maximum jobs to process}';

    protected $description = 'Dispatch all pending fiscal processing jobs (one-shot)';

    public function handle(ProcessFiscalJobsUseCase $useCase): int
    {
        $limit = (int) $this->option('limit');

        $this->info('Dispatching pending fiscal jobs...');

        $processed = $useCase->execute($limit);

        $this->info("Processed {$processed} jobs.");

        return self::SUCCESS;
    }
}
