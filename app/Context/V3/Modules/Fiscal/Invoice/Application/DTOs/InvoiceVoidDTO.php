<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Invoice\Application\DTOs;

final class InvoiceVoidDTO
{
    public function __construct(
        public readonly int $legacyId,
        public readonly string $reasonCode,
        public readonly string $reasonNote,
    ) {}
}
