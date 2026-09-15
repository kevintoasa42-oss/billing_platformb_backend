<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\InvoiceDraft\Infrastructure\Mappers;

use App\Context\V3\Modules\Fiscal\InvoiceDraft\Domain\Models\InvoiceDraft;
use App\Context\V3\Modules\Fiscal\InvoiceDraft\Domain\Models\InvoiceDraftSummary;
use App\Context\V3\Modules\Fiscal\InvoiceDraft\Infrastructure\Laravel\Eloquent\Models\InvoiceDraftModel;

final class InvoiceDraftMapper
{
    public function toDomain(InvoiceDraftModel $model): InvoiceDraft
    {
        $payload = $model->getAttribute('payload');
        $payload = is_array($payload) ? $payload : [];
        $updatedAt = $model->getAttribute('updated_at');
        $expiresAt = $model->getAttribute('expires_at');
        $ageSeconds = $updatedAt === null ? 0 : (int) abs($updatedAt->diffInSeconds(now()));

        return new InvoiceDraft(
            publicId: (string) $model->getAttribute('public_id'),
            revision: (int) $model->getAttribute('revision'),
            payload: $payload,
            summary: InvoiceDraftSummary::fromPayload($payload, $ageSeconds),
            updatedAt: $updatedAt?->toIso8601String() ?? now()->toIso8601String(),
            expiresAt: $expiresAt?->toIso8601String() ?? now()->toIso8601String(),
        );
    }
}
