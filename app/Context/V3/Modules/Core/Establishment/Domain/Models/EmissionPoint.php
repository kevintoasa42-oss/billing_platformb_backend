<?php

namespace App\Context\V3\Modules\Core\Establishment\Domain\Models;

class EmissionPoint
{
    public function __construct(
        public readonly string $establishmentId,
        public readonly string $sriCode,
        public readonly ?string $id = null,
        public readonly ?string $tenantId = null,
        public readonly ?string $name = null,
        public readonly ?string $legacyId = null,
        public readonly ?bool $isActive = null,
        public readonly ?bool $isDefault = null,
        public readonly ?bool $hasTaxValidity = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            establishmentId: $data['establishment_id'],
            sriCode: $data['sri_code'],
            id: $data['id'],
            tenantId: $data['tenant_id'],
            name: $data['name'] ?? null,
            legacyId: $data['legacy_id'] ?? null,
            isActive: $data['is_active'] ?? null,
            isDefault: $data['is_default'] ?? null,
            hasTaxValidity: $data['has_tax_validity'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenantId,
            'establishment_id' => $this->establishmentId,
            'sri_code' => $this->sriCode,
            'name' => $this->name,
            'legacy_id' => $this->legacyId,
            'is_active' => $this->isActive,
            'is_default' => $this->isDefault,
            'has_tax_validity' => $this->hasTaxValidity,
        ];
    }
}
