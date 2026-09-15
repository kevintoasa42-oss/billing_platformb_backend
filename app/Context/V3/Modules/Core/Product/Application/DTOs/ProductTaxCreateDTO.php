<?php

namespace App\Context\V3\Modules\Core\Product\Application\DTOs;

class ProductTaxCreateDTO
{
    public function __construct(
        public readonly int $sriIvaTypeId,
        public readonly ?string $taxName = null,
        public readonly ?string $percentage = null,
        public readonly ?string $sriCode = null,
        public readonly ?int $sriIvaPercentageId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sriIvaTypeId: (int) $data['sri_iva_type_id'],
            taxName: $data['tax_name'] ?? null,
            percentage: isset($data['percentage']) ? (string) $data['percentage'] : null,
            sriCode: $data['sri_code'] ?? null,
            sriIvaPercentageId: isset($data['sri_iva_percentage_id']) ? (int) $data['sri_iva_percentage_id'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'sri_iva_type_id' => $this->sriIvaTypeId,
            'tax_name' => $this->taxName,
            'percentage' => $this->percentage,
            'sri_code' => $this->sriCode,
            'sri_iva_percentage_id' => $this->sriIvaPercentageId,
        ], fn ($value): bool => $value !== null);
    }
}
