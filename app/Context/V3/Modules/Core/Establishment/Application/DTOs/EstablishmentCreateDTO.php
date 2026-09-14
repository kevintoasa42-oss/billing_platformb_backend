<?php

namespace App\Context\V3\Modules\Core\Establishment\Application\DTOs;

class EstablishmentCreateDTO
{
    public function __construct(
        public readonly string $companyId,
        public readonly string $sriCode,
        public readonly string $name,
        public readonly ?string $id = null,
        public readonly ?string $tenantId = null,
        public readonly ?string $legacyId = null,
        public readonly ?string $branchCode = null,
        public readonly ?string $address = null,
        public readonly ?string $phone = null,
        public readonly ?string $email = null,
        public readonly ?int $cityId = null,
        public readonly ?bool $isActive = null,
        /** @var array<int, string> */
        public readonly array $activityIds = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            companyId: $data['company_id'],
            sriCode: $data['sri_code'],
            name: $data['name'],
            id: $data['id'] ?? null,
            tenantId: $data['tenant_id'] ?? null,
            legacyId: $data['legacy_id'] ?? null,
            branchCode: $data['branch_code'] ?? null,
            address: $data['address'] ?? null,
            phone: $data['phone'] ?? null,
            email: $data['email'] ?? null,
            cityId: $data['city_id'] ?? null,
            isActive: $data['is_active'] ?? null,
            activityIds: $data['activity_ids'] ?? [],
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
