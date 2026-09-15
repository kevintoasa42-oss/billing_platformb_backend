<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Sri\Domain\Models;

/**
 * Plain PHP domain model for a document artifact (XML, signed XML, PDF, etc.).
 */
final class SriDocumentArtifact
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $documentId,
        public readonly string $kind,
        public readonly string $contentType,
        public readonly ?string $content,
        public readonly ?string $sha256,
        public readonly ?string $createdAt,
    ) {}
}
