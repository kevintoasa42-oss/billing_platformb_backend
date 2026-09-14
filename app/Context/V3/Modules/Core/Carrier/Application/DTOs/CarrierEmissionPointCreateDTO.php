<?php

namespace App\Context\V3\Modules\Core\Carrier\Application\DTOs;

class CarrierEmissionPointCreateDTO
{
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $tenantId = null,
        public readonly string $establishmentId,
        public readonly string $sriCode,
        public readonly ?string $name = null,
        public readonly ?bool $isActive = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            tenantId: $data['tenant_id'] ?? null,
            establishmentId: $data['establishment_id'],
            sriCode: $data['sri_code'],
            name: $data['name'] ?? null,
            isActive: $data['is_active'] ?? null,
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
            'establishment_id' => $this->establishmentId,
            'sri_code' => $this->sriCode,
            'name' => $this->name,
            'is_active' => $this->isActive,
        ];
    }
}
