<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Invoice\Application\DTOs;

final class InvoiceQueryDTO
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function __construct(
        public readonly ?string $status = null,
        public readonly int $perPage = 15,
        public readonly int $page = 1,
        public readonly array $filters = [],
    ) {}

    /**
     * @param  array<string, mixed>  $input
     */
    public static function fromArray(array $input): self
    {
        return new self(
            status: $input['status'] ?? null,
            perPage: (int) ($input['per_page'] ?? 15),
            page: (int) ($input['page'] ?? 1),
            filters: $input,
        );
    }
}
