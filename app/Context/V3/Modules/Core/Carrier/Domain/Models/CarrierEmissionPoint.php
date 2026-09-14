<?php

namespace App\Context\V3\Modules\Core\Carrier\Domain\Models;

class CarrierEmissionPoint
{
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $tenantId = null,
        public readonly string $establishmentId,
        public readonly string $sriCode,
        public readonly ?string $name = null,
        public readonly ?int $nextSequential = null,
        public readonly ?bool $isActive = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            tenantId: $data['tenant_id'] ?? null,
            establishmentId: $data['establishment_id'],
            sriCode: $data['sri_code'],
            name: $data['name'] ?? null,
            nextSequential: $data['next_sequential'] ?? null,
            isActive: $data['is_active'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenantId,
            'establishment_id' => $this->establishmentId,
            'sri_code' => $this->sriCode,
            'name' => $this->name,
            'next_sequential' => $this->nextSequential,
            'is_active' => $this->isActive,
        ];
    }
}
