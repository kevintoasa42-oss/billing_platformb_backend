<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Worker\Infrastructure\Mappers;

use App\Context\V3\Modules\Fiscal\Worker\Domain\Models\FiscalOutbox;
use App\Context\V3\Modules\Fiscal\Worker\Infrastructure\Laravel\Eloquent\Models\FiscalOutboxModel;

final class FiscalOutboxMapper
{
    public function toDomain(FiscalOutboxModel $record): FiscalOutbox
    {
        return new FiscalOutbox(
            id: (string) $record->id,
            tenantId: (string) $record->tenant_id,
            documentId: (string) $record->document_id,
            operation: (string) $record->operation,
            status: (string) $record->status,
            availableAt: $record->available_at?->toIso8601String(),
        );
    }
}
