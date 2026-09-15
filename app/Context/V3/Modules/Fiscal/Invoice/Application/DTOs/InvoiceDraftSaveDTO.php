<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Invoice\Application\DTOs;

final class InvoiceDraftSaveDTO
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public readonly ?string $publicId,
        public readonly ?int $revision,
        public readonly array $payload,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     */
    public static function fromArray(array $input): self
    {
        return new self(
            publicId: $input['public_id'] ?? null,
            revision: isset($input['revision']) ? (int) $input['revision'] : null,
            payload: is_array($input['payload'] ?? null) ? $input['payload'] : [],
        );
    }
}
