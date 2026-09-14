<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Invoice\Infrastructure\Mappers;

use App\Context\V3\Modules\Fiscal\Invoice\Domain\Models\InvoiceDraft;
use App\Context\V3\Modules\Fiscal\Invoice\Infrastructure\Laravel\Eloquent\Models\InvoiceDraftModel;

/**
 * Maps a loaded InvoiceDraftModel into a Domain InvoiceDraft.
 * Performs no database queries.
 */
final class InvoiceDraftMapper
{
    public function toDomain(InvoiceDraftModel $record): InvoiceDraft
    {
        $payload = is_array($record->payload) ? $record->payload : [];

        return new InvoiceDraft(
            id: (string) $record->id,
            publicId: (string) $record->public_id,
            revision: (int) $record->revision,
            payload: $payload,
            summary: InvoiceDraft::summaryFromPayload($payload),
            expiresAt: $record->expires_at?->toIso8601String(),
            updatedAt: $record->updated_at?->toIso8601String(),
        );
    }
}
