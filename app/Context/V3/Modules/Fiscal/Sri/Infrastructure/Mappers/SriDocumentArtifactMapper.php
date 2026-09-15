<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Sri\Infrastructure\Mappers;

use App\Context\V3\Modules\Fiscal\Invoice\Infrastructure\Laravel\Eloquent\Models\DocumentArtifactModel;
use App\Context\V3\Modules\Fiscal\Sri\Domain\Models\SriDocumentArtifact;

/**
 * Maps a DocumentArtifactModel (Eloquent) into a Domain SriDocumentArtifact.
 * Performs no database queries.
 */
final class SriDocumentArtifactMapper
{
    public function toDomain(DocumentArtifactModel $record): SriDocumentArtifact
    {
        return new SriDocumentArtifact(
            id: (string) $record->id,
            documentId: (string) $record->document_id,
            kind: (string) $record->kind,
            contentType: (string) $record->content_type,
            content: $record->content,
            sha256: $record->sha256,
            createdAt: $record->created_at?->toIso8601String(),
        );
    }
}
