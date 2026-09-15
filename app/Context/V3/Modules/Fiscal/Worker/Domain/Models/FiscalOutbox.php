<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Worker\Domain\Models;

/**
 * Plain PHP domain model for a fiscal outbox record.
 */
final class FiscalOutbox
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $tenantId,
        public readonly string $documentId,
        public readonly string $operation,
        public string $status,
        public readonly ?string $availableAt,
    ) {}
}
