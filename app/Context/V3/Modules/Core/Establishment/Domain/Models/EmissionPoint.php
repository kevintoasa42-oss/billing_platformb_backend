<?php

namespace App\Context\V3\Modules\Core\Establishment\Domain\Models;

class EmissionPoint
{
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $tenantId = null,
        public readonly ?string $establishmentId = null,
        public readonly ?string $sriCode = null,
        public readonly ?string $name = null,
        public readonly ?int $legacyId = null,
        public readonly ?int $branchLegacyId = null,
        public readonly ?bool $isActive = null,
        public readonly ?bool $isDefault = null,
        public readonly ?bool $hasTaxValidity = null,
        public readonly ?int $lastIssuedSequential = null,
        public readonly ?int $nextSequential = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            tenantId: $data['tenant_id'] ?? null,
            establishmentId: $data['establishment_id'] ?? null,
            sriCode: $data['sri_code'] ?? null,
            name: $data['name'] ?? null,
            legacyId: isset($data['legacy_id']) ? (int) $data['legacy_id'] : null,
            branchLegacyId: isset($data['branch_legacy_id']) ? (int) $data['branch_legacy_id'] : null,
            isActive: $data['is_active'] ?? null,
            isDefault: $data['is_default'] ?? null,
            hasTaxValidity: $data['has_tax_validity'] ?? null,
            lastIssuedSequential: isset($data['last_issued_sequential']) ? (int) $data['last_issued_sequential'] : null,
            nextSequential: isset($data['next_sequential']) ? (int) $data['next_sequential'] : null,
        );
    }

    /**
     * Issuance point response shape matching ArtraFiscalBackEnd pointRow.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $last = $this->lastIssuedSequential ?? 0;

        return [
            'id' => $this->legacyId,
            'branch_id' => $this->branchLegacyId,
            'name' => trim((string) ($this->name ?? '')) !== '' ? $this->name : 'Punto '.$this->sriCode,
            'issuance_point_number' => $this->sriCode,
            'is_active' => $this->isActive,
            'is_default' => $this->isDefault,
            'has_tax_validity' => $this->hasTaxValidity,
            'last_issued_sequential' => $last,
            'next_sequential' => $this->nextSequential ?? ($last + 1),
        ];
    }

    /**
     * Legacy shape for existing emission-point routes.
     *
     * @return array<string, mixed>
     */
    public function toLegacyArray(): array
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
