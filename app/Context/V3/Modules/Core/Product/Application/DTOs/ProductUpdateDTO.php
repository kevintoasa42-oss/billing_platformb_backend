<?php

namespace App\Context\V3\Modules\Core\Product\Application\DTOs;

class ProductUpdateDTO
{
    /**
     * @param  array<int>|null  $sriIvaTypeIds
     */
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $referencePrice = null,
        public readonly ?string $activityId = null,
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
            name: $data['name'] ?? null,
            referencePrice: isset($data['reference_price']) ? (string) $data['reference_price'] : (isset($data['unit_price']) ? (string) $data['unit_price'] : null),
            activityId: $data['activity_id'] ?? null,
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
        $data = [];
        if ($this->name !== null) {
            $data['name'] = $this->name;
        }
        if ($this->referencePrice !== null) {
            $data['unit_price'] = $this->referencePrice;
        }
        if ($this->activityId !== null) {
            $data['activity_id'] = $this->activityId;
        }
        if ($this->barcode !== null) {
            $data['barcode'] = $this->barcode;
        }
        if ($this->auxiliaryCode !== null) {
            $data['auxiliary_code'] = $this->auxiliaryCode;
        }
        if ($this->otherCode !== null) {
            $data['other_code'] = $this->otherCode;
        }
        if ($this->description !== null) {
            $data['description'] = $this->description;
        }
        if ($this->type !== null) {
            $data['type'] = $this->type;
        }
        if ($this->isActive !== null) {
            $data['is_active'] = $this->isActive;
        }

        return $data;
    }
}
