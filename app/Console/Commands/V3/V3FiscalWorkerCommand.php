<?php

declare(strict_types=1);

namespace App\Console\Commands\V3;

use App\Context\V3\Modules\Fiscal\Worker\Application\UseCases\ProcessFiscalJobsUseCase;
use Illuminate\Console\Command;

/**
 * Long-running fiscal worker command. Drains pending processing jobs.
 */
final class V3FiscalWorkerCommand extends Command
{
    protected $signature = 'v3:fiscal:worker
                            {--limit=100 : Maximum jobs to process per drain cycle}
                            {--sleep=3 : Seconds to sleep between cycles}
                            {--once : Run a single drain cycle and exit}';

    protected $description = 'Run the fiscal processing worker (long-running daemon)';

    public function handle(ProcessFiscalJobsUseCase $useCase): int
    {
        $limit = (int) $this->option('limit');
        $sleep = (int) $this->option('sleep');
        $once = (bool) $this->option('once');

        $this->info('Fiscal worker started. Mode: '.config('services.sri.mode', 'mock'));

        do {
            $processed = $useCase->execute($limit);

            if ($processed > 0) {
                $this->info("Processed {$processed} jobs.");
            }

            if ($once) {
                break;
            }

            if ($processed === 0) {
                sleep($sleep);
            }
        } while (true);

        $this->info('Fiscal worker stopped.');

        return self::SUCCESS;
    }
}
