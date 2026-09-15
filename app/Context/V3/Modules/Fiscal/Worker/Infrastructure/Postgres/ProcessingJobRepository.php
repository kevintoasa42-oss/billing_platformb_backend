<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Worker\Infrastructure\Postgres;

use App\Context\V3\Modules\Fiscal\Worker\Domain\Models\ProcessingJob;
use App\Context\V3\Modules\Fiscal\Worker\Domain\Repository\ProcessingJobRepositoryInterface;
use App\Context\V3\Modules\Fiscal\Worker\Infrastructure\Laravel\Eloquent\Models\ProcessingJobModel;
use App\Context\V3\Modules\Fiscal\Worker\Infrastructure\Mappers\ProcessingJobMapper;
use Illuminate\Support\Facades\DB;

/**
 * PostgreSQL implementation of the processing job repository.
 * Uses SELECT FOR UPDATE SKIP LOCKED for concurrent worker claims.
 */
final class ProcessingJobRepository implements ProcessingJobRepositoryInterface
{
    public function __construct(
        private readonly ProcessingJobMapper $mapper,
    ) {}

    public function enqueueForDocument(string $tenantId, string $documentId, int $writerEpoch): void
    {
        $operations = ProcessingJob::OPERATIONS;
        $predecessorId = null;

        foreach ($operations as $operation) {
            $id = (string) DB::connection('master_v3')->selectOne(
                "INSERT INTO integration.processing_jobs (tenant_id, document_id, operation, predecessor_id, status, available_at, writer_epoch)
                 VALUES (?, ?, ?, ?, 'pending', now(), ?)
                 RETURNING id",
                [$tenantId, $documentId, $operation, $predecessorId, $writerEpoch],
            )?->id;

            $predecessorId = $id;
        }
    }

    public function claimNext(string $tenantId, int $leaseSeconds = 300): ?ProcessingJob
    {
        // Set tenant context for RLS in the same connection
        DB::connection('master_v3')->statement(
            "SELECT set_config('app.tenant_id', ?, false)",
            [$tenantId],
        );

        $job = DB::connection('master_v3')->selectOne(
            "UPDATE integration.processing_jobs
             SET status = 'running',
                 lease_until = now() + interval '".(int) $leaseSeconds." seconds',
                 attempts = attempts + 1,
                 fencing_token = fencing_token + 1
             WHERE id = (
                 SELECT j.id
                 FROM integration.processing_jobs j
                 WHERE j.tenant_id = ?
                   AND j.status = 'pending'
                   AND j.available_at <= now()
                   AND (
                       j.predecessor_id IS NULL
                       OR EXISTS (
                           SELECT 1 FROM integration.processing_jobs p
                           WHERE p.id = j.predecessor_id AND p.status = 'completed'
                       )
                   )
                 ORDER BY j.available_at
                 FOR UPDATE OF j SKIP LOCKED
                 LIMIT 1
             )
             RETURNING *",
            [$tenantId],
        );

        if ($job === null) {
            return null;
        }

        $model = ProcessingJobModel::hydrate([$job])[0];
        $model->setConnection('pgsql');

        return $this->mapper->toDomain($model);
    }

    public function complete(string $tenantId, string $jobId): void
    {
        DB::connection('master_v3')->statement(
            "SELECT set_config('app.tenant_id', ?, false)",
            [$tenantId],
        );
        DB::connection('master_v3')->statement(
            "UPDATE integration.processing_jobs SET status = 'completed' WHERE tenant_id = ? AND id = ?",
            [$tenantId, $jobId],
        );
    }

    public function fail(string $tenantId, string $jobId, string $error, bool $retryable): void
    {
        DB::connection('master_v3')->statement(
            "SELECT set_config('app.tenant_id', ?, false)",
            [$tenantId],
        );
        if ($retryable) {
            DB::connection('master_v3')->statement(
                "UPDATE integration.processing_jobs
                 SET status = 'pending', available_at = now() + interval '60 seconds'
                 WHERE tenant_id = ? AND id = ?",
                [$tenantId, $jobId],
            );
        } else {
            DB::connection('master_v3')->statement(
                "UPDATE integration.processing_jobs SET status = 'dead' WHERE tenant_id = ? AND id = ?",
                [$tenantId, $jobId],
            );
        }
    }

    public function countPending(string $tenantId): int
    {
        return (int) DB::connection('master_v3')->scalar(
            "SELECT count(*) FROM integration.processing_jobs WHERE tenant_id = ? AND status = 'pending'",
            [$tenantId],
        );
    }
}
