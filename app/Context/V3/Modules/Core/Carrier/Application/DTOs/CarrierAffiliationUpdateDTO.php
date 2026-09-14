<?php

namespace App\Context\V3\Modules\Core\Carrier\Application\DTOs;

class CarrierAffiliationUpdateDTO
{
    public function __construct(
        public readonly ?string $validity = null,
        /** @var array<int, array<string, mixed>>|null */
        public readonly ?array $vehicleAssignments = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            validity: $data['validity'] ?? null,
            vehicleAssignments: $data['vehicle_assignments'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = array_filter([
            'validity' => $this->validity,
        ], fn ($value) => $value !== null);

        if ($this->vehicleAssignments !== null) {
            $data['vehicle_assignments'] = $this->vehicleAssignments;
        }

        return $data;
    }
}
