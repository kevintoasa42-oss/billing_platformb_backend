<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Worker\Infrastructure\Mappers;

use App\Context\V3\Modules\Fiscal\Worker\Domain\Models\DocumentEvent;
use App\Context\V3\Modules\Fiscal\Worker\Infrastructure\Laravel\Eloquent\Models\DocumentEventModel;

final class DocumentEventMapper
{
    public function toDomain(DocumentEventModel $record): DocumentEvent
    {
        return new DocumentEvent(
            id: (string) $record->id,
            tenantId: (string) $record->tenant_id,
            documentId: (string) $record->document_id,
            eventType: (string) $record->event,
            payload: null,
            createdAt: $record->created_at?->toIso8601String(),
        );
    }
}
