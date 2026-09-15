<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Worker\Infrastructure\Mappers;

use App\Context\V3\Modules\Fiscal\Worker\Domain\Models\ProcessingAttempt;
use App\Context\V3\Modules\Fiscal\Worker\Infrastructure\Laravel\Eloquent\Models\ProcessingAttemptModel;

final class ProcessingAttemptMapper
{
    public function toDomain(ProcessingAttemptModel $record): ProcessingAttempt
    {
        return new ProcessingAttempt(
            id: (string) $record->id,
            tenantId: (string) $record->tenant_id,
            jobId: (string) $record->job_id,
            attemptNumber: (int) $record->attempt_number,
            status: (string) $record->status,
            error: $record->error,
            startedAt: $record->started_at?->toIso8601String(),
            finishedAt: $record->finished_at?->toIso8601String(),
        );
    }
}
