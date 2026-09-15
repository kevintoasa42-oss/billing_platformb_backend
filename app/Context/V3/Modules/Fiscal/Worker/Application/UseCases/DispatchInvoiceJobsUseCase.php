<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Worker\Application\UseCases;

use App\Context\V3\Modules\Fiscal\Worker\Domain\Repository\ProcessingJobRepositoryInterface;

/**
 * Enqueues the 7 sequential processing jobs for a document after creation.
 */
final class DispatchInvoiceJobsUseCase
{
    public function __construct(
        private readonly ProcessingJobRepositoryInterface $jobRepository,
    ) {}

    public function execute(string $tenantId, string $documentId, int $writerEpoch): void
    {
        $this->jobRepository->enqueueForDocument($tenantId, $documentId, $writerEpoch);
    }
}
