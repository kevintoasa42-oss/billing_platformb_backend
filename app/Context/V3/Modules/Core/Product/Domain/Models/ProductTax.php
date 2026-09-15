<?php

namespace App\Context\V3\Modules\Core\Product\Domain\Models;

class ProductTax
{
    /**
     * @param  array<string, mixed>|null  $sriIvaType
     */
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?int $productId = null,
        public readonly ?int $sriIvaTypeId = null,
        public readonly ?string $taxName = null,
        public readonly ?string $percentage = null,
        public readonly ?string $sriCode = null,
        public readonly ?bool $isActive = null,
        public readonly ?array $sriIvaType = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            productId: isset($data['product_id']) ? (int) $data['product_id'] : null,
            sriIvaTypeId: isset($data['sri_iva_type_id']) ? (int) $data['sri_iva_type_id'] : null,
            taxName: $data['tax_name'] ?? null,
            percentage: $data['percentage'] ?? null,
            sriCode: $data['sri_code'] ?? null,
            isActive: $data['is_active'] ?? null,
            sriIvaType: $data['sri_iva_type'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->productId,
            'sri_iva_type_id' => $this->sriIvaTypeId,
            'tax_name' => $this->taxName,
            'percentage' => $this->percentage,
            'sri_code' => $this->sriCode,
            'is_active' => $this->isActive,
            'sri_iva_type' => $this->sriIvaType,
        ];
    }
}
