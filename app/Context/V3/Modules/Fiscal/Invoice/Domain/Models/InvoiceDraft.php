<?php

namespace App\Context\V3\Modules\Fiscal\Invoice\Domain\Models;

class InvoiceDraft
{
    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $summary
     */
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $publicId = null,
        public readonly ?int $revision = null,
        public readonly array $payload = [],
        public readonly array $summary = [],
        public readonly ?string $expiresAt = null,
        public readonly ?string $updatedAt = null,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->publicId,
            'public_id' => $this->publicId,
            'revision' => $this->revision,
            'payload' => $this->payload,
            'summary' => $this->summary,
            'expires_at' => $this->expiresAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    /**
     * Build a summary from a payload array.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public static function summaryFromPayload(array $payload): array
    {
        $items = is_array($payload['items'] ?? null) ? $payload['items'] : [];
        $client = is_array($payload['client'] ?? null) ? $payload['client'] : [];

        return [
            'client_name' => (string) ($client['name'] ?? ''),
            'product_count' => count($items),
        ];
    }
}
