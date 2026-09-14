<?php

namespace App\Context\V3\Modules\Core\Establishment\Application\DTOs;

class EmissionPointUpdateDTO
{
    public function __construct(
        public readonly ?string $sriCode = null,
        public readonly ?string $name = null,
        public readonly ?bool $isActive = null,
        public readonly ?bool $isDefault = null,
        public readonly ?bool $hasTaxValidity = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            sriCode: $data['sri_code'] ?? null,
            name: $data['name'] ?? null,
            isActive: $data['is_active'] ?? null,
            isDefault: $data['is_default'] ?? null,
            hasTaxValidity: $data['has_tax_validity'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'sri_code' => $this->sriCode,
            'name' => $this->name,
            'is_active' => $this->isActive,
            'is_default' => $this->isDefault,
            'has_tax_validity' => $this->hasTaxValidity,
        ], fn ($value) => $value !== null);
    }
}
