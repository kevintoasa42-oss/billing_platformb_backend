<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Worker\Application\UseCases;

use App\Context\V3\Modules\Fiscal\Worker\Infrastructure\Postgres\FiscalWorkerRepository;
use Illuminate\Support\Facades\DB;

/**
 * Orchestrates the drain of pending fiscal jobs for all active tenants.
 */
final class ProcessFiscalJobsUseCase
{
    public function __construct(
        private readonly FiscalWorkerRepository $workerRepository,
    ) {}

    public function execute(int $limit = 100): int
    {
        $totalProcessed = 0;

        $tenantIds = DB::connection('master_v3')->table('platform.tenants')->pluck('id');

        foreach ($tenantIds as $tenantId) {
            // Set tenant context for RLS (session-level)
            DB::connection('master_v3')->statement(
                "SELECT set_config('app.tenant_id', ?, false)",
                [(string) $tenantId],
            );

            // Check if this tenant has pending jobs
            $pendingCount = (int) DB::connection('master_v3')->scalar(
                "SELECT count(*) FROM integration.processing_jobs WHERE tenant_id = ? AND status = 'pending'",
                [(string) $tenantId],
            );

            if ($pendingCount === 0) {
                continue;
            }

            $processed = $this->workerRepository->drain((string) $tenantId, $limit);
            $totalProcessed += $processed;
        }

        return $totalProcessed;
    }
}
