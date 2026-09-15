<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\InvoiceDraft\Domain\Repository;

use App\Context\V3\Modules\Fiscal\InvoiceDraft\Domain\Models\InvoiceDraft;

interface InvoiceDraftRepositoryInterface
{
    /** @param array<string, mixed> $payload */
    public function save(string $userId, array $payload, ?int $expectedRevision): InvoiceDraft;
}
