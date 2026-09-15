<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Worker\Domain\Models;

/**
 * Plain PHP domain model for a document event (audit trail).
 */
final class DocumentEvent
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $tenantId,
        public readonly string $documentId,
        public readonly string $eventType,
        public readonly ?array $payload,
        public readonly ?string $createdAt,
    ) {}
}
