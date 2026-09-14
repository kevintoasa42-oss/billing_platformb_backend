<?php

namespace App\Context\V3\Modules\Core\Establishment\Domain\Models;

class Establishment
{
    /**
     * @param  array<int, array<string, mixed>>|null  $issuancePoints
     * @param  array<int, string>|null  $activityIds
     */
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $tenantId = null,
        public readonly ?string $companyId = null,
        public readonly ?string $sriCode = null,
        public readonly ?string $name = null,
        public readonly ?int $legacyId = null,
        public readonly ?string $branchCode = null,
        public readonly ?string $address = null,
        public readonly ?string $phone = null,
        public readonly ?string $email = null,
        public readonly ?int $cityId = null,
        public readonly ?bool $isActive = null,
        public readonly ?array $activityIds = null,
        public readonly ?array $issuancePoints = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            tenantId: $data['tenant_id'] ?? null,
            companyId: $data['company_id'] ?? null,
            sriCode: $data['sri_code'] ?? null,
            name: $data['name'] ?? null,
            legacyId: isset($data['legacy_id']) ? (int) $data['legacy_id'] : null,
            branchCode: $data['branch_code'] ?? null,
            address: $data['address'] ?? null,
            phone: $data['phone'] ?? null,
            email: $data['email'] ?? null,
            cityId: isset($data['city_id']) ? (int) $data['city_id'] : null,
            isActive: $data['is_active'] ?? null,
            activityIds: $data['activity_ids'] ?? null,
            issuancePoints: $data['issuance_points'] ?? null,
        );
    }

    /**
     * Branch response shape matching ArtraFiscalBackEnd branchRow.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->legacyId,
            'name' => $this->name,
            'branch_code' => $this->branchCode ?? $this->sriCode,
            'sri_establishment_number' => $this->sriCode,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'city_id' => $this->cityId,
            'is_active' => $this->isActive,
            'issuance_points' => $this->issuancePoints ?? [],
        ];
    }

    /**
     * Legacy shape for existing establishment routes.
     *
     * @return array<string, mixed>
     */
    public function toLegacyArray(): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenantId,
            'company_id' => $this->companyId,
            'sri_code' => $this->sriCode,
            'name' => $this->name,
            'legacy_id' => $this->legacyId,
            'branch_code' => $this->branchCode,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'city_id' => $this->cityId,
            'is_active' => $this->isActive,
            'activity_ids' => $this->activityIds,
        ];
    }
}
