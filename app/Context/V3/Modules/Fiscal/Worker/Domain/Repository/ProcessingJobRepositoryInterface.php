<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Worker\Domain\Repository;

use App\Context\V3\Modules\Fiscal\Worker\Domain\Models\ProcessingJob;

/**
 * Contract for the processing job repository.
 * Implemented by the Postgres repository in Infrastructure.
 */
interface ProcessingJobRepositoryInterface
{
    /**
     * Enqueue the 7 sequential jobs for a document.
     *
     * @param  string  $tenantId
     * @param  string  $documentId
     * @param  int  $writerEpoch
     * @return void
     */
    public function enqueueForDocument(string $tenantId, string $documentId, int $writerEpoch): void;

    /**
     * Claim the next eligible job for a tenant.
     *
     * @param  string  $tenantId
     * @param  int  $leaseSeconds
     * @return ProcessingJob|null
     */
    public function claimNext(string $tenantId, int $leaseSeconds = 300): ?ProcessingJob;

    /**
     * Mark a job as completed.
     */
    public function complete(string $tenantId, string $jobId): void;

    /**
     * Mark a job as failed.
     */
    public function fail(string $tenantId, string $jobId, string $error, bool $retryable): void;

    /**
     * Count pending jobs for a tenant.
     */
    public function countPending(string $tenantId): int;
}
