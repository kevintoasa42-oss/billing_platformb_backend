<?php

namespace App\Context\V3\Modules\Core\Product\Application\DTOs;

class ProductCreateDTO
{
    /**
     * @param  array<int>|null  $sriIvaTypeIds
     */
    public function __construct(
        public readonly string $name,
        public readonly string $referencePrice,
        public readonly string $activityId,
        public readonly ?string $barcode = null,
        public readonly ?string $auxiliaryCode = null,
        public readonly ?string $otherCode = null,
        public readonly ?string $description = null,
        public readonly ?string $type = null,
        public readonly ?bool $isActive = null,
        public readonly ?array $sriIvaTypeIds = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            referencePrice: (string) ($data['reference_price'] ?? $data['unit_price'] ?? '0'),
            activityId: $data['activity_id'],
            barcode: $data['barcode'] ?? null,
            auxiliaryCode: $data['auxiliary_code'] ?? null,
            otherCode: $data['other_code'] ?? null,
            description: $data['description'] ?? null,
            type: $data['type'] ?? null,
            isActive: $data['is_active'] ?? null,
            sriIvaTypeIds: $data['sri_iva_type_ids'] ?? null,
        );
    }

    public function toDatabaseArray(): array
    {
        $data = array_filter([
            'name' => $this->name,
            'unit_price' => $this->referencePrice,
            'activity_id' => $this->activityId,
            'barcode' => $this->barcode,
            'auxiliary_code' => $this->auxiliaryCode,
            'other_code' => $this->otherCode,
            'description' => $this->description,
            'type' => $this->type ?? 'product',
            'is_active' => $this->isActive ?? true,
        ], fn ($value): bool => $value !== null);

        if ($this->sriIvaTypeIds !== null) {
            $data['sri_iva_type_ids'] = $this->sriIvaTypeIds;
        }

        return $data;
    }
}
