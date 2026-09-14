<?php

namespace App\Context\V3\Modules\Core\Vehicle\Domain\Models;

class Vehicle
{
    public function __construct(
        public readonly string $plate,
        public readonly ?string $id = null,
        public readonly ?string $tenantId = null,
        public readonly ?string $legacyId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            plate: $data['plate'],
            id: $data['id'],
            tenantId: $data['tenant_id'],
            legacyId: $data['legacy_id'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenantId,
            'plate' => $this->plate,
            'legacy_id' => $this->legacyId,
        ];
    }
}
