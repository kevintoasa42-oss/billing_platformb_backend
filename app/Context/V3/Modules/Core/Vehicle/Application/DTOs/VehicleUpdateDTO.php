<?php

namespace App\Context\V3\Modules\Core\Vehicle\Application\DTOs;

class VehicleUpdateDTO
{
    public function __construct(
        public readonly ?string $plate = null,
        public readonly ?string $legacyId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            plate: $data['plate'] ?? null,
            legacyId: $data['legacy_id'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'plate' => $this->plate,
            'legacy_id' => $this->legacyId,
        ], fn ($value) => $value !== null);
    }
}
