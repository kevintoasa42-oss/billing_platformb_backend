<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Worker\Infrastructure\Mappers;

use App\Context\V3\Modules\Fiscal\Worker\Domain\Models\ProcessingJob;
use App\Context\V3\Modules\Fiscal\Worker\Infrastructure\Laravel\Eloquent\Models\ProcessingJobModel;

/**
 * Maps a ProcessingJobModel (Eloquent) into a Domain ProcessingJob.
 * Performs no database queries.
 */
final class ProcessingJobMapper
{
    public function toDomain(ProcessingJobModel $record): ProcessingJob
    {
        return new ProcessingJob(
            id: (string) $record->id,
            tenantId: (string) $record->tenant_id,
            documentId: (string) $record->document_id,
            operation: (string) $record->operation,
            predecessorId: $record->predecessor_id !== null ? (string) $record->predecessor_id : null,
            status: (string) $record->status,
            availableAt: $record->available_at?->toIso8601String(),
            leaseUntil: $record->lease_until?->toIso8601String(),
            fencingToken: $record->fencing_token !== null ? (string) $record->fencing_token : null,
            writerEpoch: (int) $record->writer_epoch,
            attempts: (int) $record->attempts,
            maxAttempts: (int) $record->max_attempts,
        );
    }
}
