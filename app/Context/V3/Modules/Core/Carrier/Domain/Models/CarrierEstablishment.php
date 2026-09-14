<?php

namespace App\Context\V3\Modules\Core\Carrier\Domain\Models;

class CarrierEstablishment
{
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $tenantId = null,
        public readonly string $carrierCompanyId,
        public readonly string $sriCode,
        public readonly string $name,
        public readonly ?string $address = null,
        public readonly ?string $phone = null,
        public readonly ?string $email = null,
        public readonly ?int $cityId = null,
        public readonly ?bool $isActive = null,
        /** @var array<int, string>|null */
        public readonly ?array $activityIds = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            tenantId: $data['tenant_id'] ?? null,
            carrierCompanyId: $data['carrier_company_id'],
            sriCode: $data['sri_code'],
            name: $data['name'],
            address: $data['address'] ?? null,
            phone: $data['phone'] ?? null,
            email: $data['email'] ?? null,
            cityId: $data['city_id'] ?? null,
            isActive: $data['is_active'] ?? null,
            activityIds: $data['activity_ids'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenantId,
            'carrier_company_id' => $this->carrierCompanyId,
            'sri_code' => $this->sriCode,
            'name' => $this->name,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'city_id' => $this->cityId,
            'is_active' => $this->isActive,
            'activity_ids' => $this->activityIds,
        ];
    }
}
