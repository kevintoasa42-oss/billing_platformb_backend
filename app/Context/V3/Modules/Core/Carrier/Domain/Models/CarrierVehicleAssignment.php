<?php

namespace App\Context\V3\Modules\Core\Carrier\Domain\Models;

class CarrierVehicleAssignment
{
    public function __construct(
        public readonly string $id,
        public readonly string $tenantId,
        public readonly string $affiliationId,
        public readonly string $vehicleId,
        public readonly ?string $validity = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            tenantId: $data['tenant_id'],
            affiliationId: $data['affiliation_id'],
            vehicleId: $data['vehicle_id'],
            validity: $data['validity'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenantId,
            'affiliation_id' => $this->affiliationId,
            'vehicle_id' => $this->vehicleId,
            'validity' => $this->validity,
        ];
    }
}
