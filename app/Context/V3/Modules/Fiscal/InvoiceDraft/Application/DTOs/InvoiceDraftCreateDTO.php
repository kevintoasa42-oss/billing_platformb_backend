<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\InvoiceDraft\Application\DTOs;

final readonly class InvoiceDraftCreateDTO
{
    /** @param array<string, mixed> $payload */
    public function __construct(
        public array $payload,
        public ?int $revision,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            payload: is_array($data['payload'] ?? null) ? $data['payload'] : [],
            revision: array_key_exists('revision', $data) && $data['revision'] !== null
                ? (int) $data['revision']
                : null,
        );
    }
}
