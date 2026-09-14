<?php

namespace App\Context\V3\Modules\Core\SriIva\Application\DTOs;

class SriIvaPercentageUpdateDTO
{
    public function __construct(
        public readonly ?string $percentage = null,
        public readonly ?string $startDate = null,
        public readonly ?string $endDate = null,
        public readonly ?string $code = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            percentage: $data['percentage'] ?? null,
            startDate: $data['start_date'] ?? null,
            endDate: $data['end_date'] ?? null,
            code: $data['code'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'percentage' => $this->percentage,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'code' => $this->code,
        ], fn ($value): bool => $value !== null);
    }
}
