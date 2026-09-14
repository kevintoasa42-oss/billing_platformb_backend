<?php

namespace App\Context\V3\Modules\Core\Carrier\Application\DTOs;

class CarrierAffiliationCreateDTO
{
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $tenantId = null,
        public readonly string $thirdPartyId,
        public readonly ?string $validity = null,
        /** @var array<int, array<string, mixed>> */
        public readonly array $vehicleAssignments = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            tenantId: $data['tenant_id'] ?? null,
            thirdPartyId: $data['third_party_id'],
            validity: $data['validity'] ?? null,
            vehicleAssignments: $data['vehicle_assignments'] ?? [],
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
            'third_party_id' => $this->thirdPartyId,
            'validity' => $this->validity,
            'vehicle_assignments' => $this->vehicleAssignments,
        ];
    }
}
