<?php

namespace App\Context\V3\Modules\Core\SriIva\Domain\Models;

class SriIvaPercentage
{
    public function __construct(
        public readonly int $id,
        public readonly int $sriIvaTypeId,
        public readonly string $percentage,
        public readonly string $startDate,
        public readonly ?string $endDate,
        public readonly string $code,
        public readonly bool $isActive,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            sriIvaTypeId: (int) $data['sri_iva_type_id'],
            percentage: (string) $data['percentage'],
            startDate: (string) $data['start_date'],
            endDate: $data['end_date'] ?? null,
            code: (string) $data['code'],
            isActive: (bool) $data['is_active'],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'sri_iva_type_id' => $this->sriIvaTypeId,
            'percentage' => $this->percentage,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'code' => $this->code,
            'is_active' => $this->isActive,
        ];
    }
}
