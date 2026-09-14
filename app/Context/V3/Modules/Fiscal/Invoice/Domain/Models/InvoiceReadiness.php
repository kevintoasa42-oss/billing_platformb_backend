<?php

namespace App\Context\V3\Modules\Fiscal\Invoice\Domain\Models;

class InvoiceReadiness
{
    /**
     * @param  array<int, string>  $blockers
     * @param  array<string, mixed>|null  $sequential
     */
    public function __construct(
        public readonly bool $ready = false,
        public readonly string $mode = 'mock',
        public readonly array $blockers = [],
        public readonly ?array $sequential = null,
    ) {}

    public function toArray(): array
    {
        return [
            'ready' => $this->ready,
            'mode' => $this->mode,
            'blockers' => $this->blockers,
            'sequential' => $this->sequential,
        ];
    }
}
