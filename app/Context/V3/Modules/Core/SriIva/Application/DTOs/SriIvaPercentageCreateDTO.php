<?php

namespace App\Context\V3\Modules\Core\SriIva\Application\DTOs;

class SriIvaPercentageCreateDTO
{
    public function __construct(
        public readonly int $sriIvaTypeId,
        public readonly string $percentage,
        public readonly string $startDate,
        public readonly ?string $endDate,
        public readonly string $code,
    ) {}

    public static function fromArray(array $data, int $sriIvaTypeId): self
    {
        return new self(
            sriIvaTypeId: $sriIvaTypeId,
            percentage: $data['percentage'],
            startDate: $data['start_date'],
            endDate: $data['end_date'] ?? null,
            code: $data['code'] ?? (string) $data['percentage'],
        );
    }

    public function toArray(): array
    {
        return [
            'sri_iva_type_id' => $this->sriIvaTypeId,
            'percentage' => $this->percentage,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'code' => $this->code,
        ];
    }
}
