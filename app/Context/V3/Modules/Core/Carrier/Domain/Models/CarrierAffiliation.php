<?php

namespace App\Context\V3\Modules\Core\Carrier\Domain\Models;

class CarrierAffiliation
{
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $tenantId = null,
        public readonly string $thirdPartyId,
        public readonly ?string $validity = null,
        /** @var array<int, array<string, mixed>>|null */
        public readonly ?array $vehicleAssignments = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            tenantId: $data['tenant_id'],
            thirdPartyId: $data['third_party_id'],
            validity: $data['validity'] ?? null,
            vehicleAssignments: $data['vehicle_assignments'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenantId,
            'third_party_id' => $this->thirdPartyId,
            'validity' => $this->validity,
            'vehicle_assignments' => $this->vehicleAssignments,
        ];
    }
}
