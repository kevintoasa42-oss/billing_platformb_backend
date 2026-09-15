<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Worker\Domain\Models;

/**
 * Plain PHP domain model for a processing attempt.
 */
final class ProcessingAttempt
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $tenantId,
        public readonly string $jobId,
        public readonly int $attemptNumber,
        public readonly string $status,
        public readonly ?string $error,
        public readonly ?string $startedAt,
        public readonly ?string $finishedAt,
    ) {}
}
