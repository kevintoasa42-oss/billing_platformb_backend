<?php

namespace App\Context\V3\Modules\Core\Product\Domain\Models;

class Product
{
    /**
     * @param  array<int, mixed>  $taxes
     */
    public function __construct(
        public readonly ?string $uuid = null,
        public readonly ?int $id = null,
        public readonly ?string $name = null,
        public readonly ?string $referencePrice = null,
        public readonly ?string $unitPrice = null,
        public readonly ?string $sriPrincipalCode = null,
        public readonly ?bool $isActive = null,
        public readonly ?string $type = null,
        public readonly ?string $barcode = null,
        public readonly ?string $auxiliaryCode = null,
        public readonly ?string $otherCode = null,
        public readonly ?string $description = null,
        public readonly ?string $activityId = null,
        public readonly array $taxes = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'] ?? $data['id'] ?? null,
            id: isset($data['legacy_id']) ? (int) $data['legacy_id'] : null,
            name: $data['name'] ?? null,
            referencePrice: $data['reference_price'] ?? $data['unit_price'] ?? null,
            unitPrice: $data['unit_price'] ?? $data['reference_price'] ?? null,
            sriPrincipalCode: $data['sri_principal_code'] ?? null,
            isActive: $data['is_active'] ?? null,
            type: $data['type'] ?? null,
            barcode: $data['barcode'] ?? null,
            auxiliaryCode: $data['auxiliary_code'] ?? null,
            otherCode: $data['other_code'] ?? null,
            description: $data['description'] ?? null,
            activityId: $data['activity_id'] ?? null,
            taxes: $data['taxes'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'reference_price' => $this->referencePrice ?? $this->unitPrice,
            'unit_price' => $this->unitPrice ?? $this->referencePrice,
            'sri_principal_code' => $this->sriPrincipalCode,
            'is_active' => $this->isActive,
            'type' => $this->type,
            'barcode' => $this->barcode,
            'auxiliary_code' => $this->auxiliaryCode,
            'other_code' => $this->otherCode,
            'description' => $this->description,
            'activity_id' => $this->activityId,
            'taxes' => $this->taxes,
        ];
    }
}
