<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\InvoiceDraft\Domain\Models;

/**
 * @param  array<string, mixed>  $payload
 * @param  array{client_name: string, product_count: int, total: string, age_seconds: int}  $summary
 */
final readonly class InvoiceDraft
{
    public function __construct(
        public string $publicId,
        public int $revision,
        public array $payload,
        public array $summary,
        public string $updatedAt,
        public string $expiresAt,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->publicId,
            'public_id' => $this->publicId,
            'revision' => $this->revision,
            'payload' => $this->payload,
            'summary' => $this->summary,
            'updated_at' => $this->updatedAt,
            'expires_at' => $this->expiresAt,
        ];
    }
}
