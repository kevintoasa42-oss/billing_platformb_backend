<?php

namespace App\Context\V3\Modules\Core\Carrier\Domain\Models;

class CarrierCompany
{
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $tenantId = null,
        public readonly string $thirdPartyId,
        public readonly string $legalName,
        public readonly ?string $tradeName = null,
        public readonly ?bool $isActive = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            tenantId: $data['tenant_id'] ?? null,
            thirdPartyId: $data['third_party_id'],
            legalName: $data['legal_name'],
            tradeName: $data['trade_name'] ?? null,
            isActive: $data['is_active'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenantId,
            'third_party_id' => $this->thirdPartyId,
            'legal_name' => $this->legalName,
            'trade_name' => $this->tradeName,
            'is_active' => $this->isActive,
        ];
    }
}
