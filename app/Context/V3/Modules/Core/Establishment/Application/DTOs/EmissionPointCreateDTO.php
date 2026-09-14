<?php

namespace App\Context\V3\Modules\Core\Establishment\Application\DTOs;

class EmissionPointCreateDTO
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

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            establishmentId: $data['establishment_id'],
            sriCode: $data['sri_code'],
            id: $data['id'] ?? null,
            tenantId: $data['tenant_id'] ?? null,
            name: $data['name'] ?? null,
            legacyId: $data['legacy_id'] ?? null,
            isActive: $data['is_active'] ?? null,
            isDefault: $data['is_default'] ?? null,
            hasTaxValidity: $data['has_tax_validity'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
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
