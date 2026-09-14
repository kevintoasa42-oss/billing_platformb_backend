<?php

namespace App\Context\V3\Modules\Core\SriIva\Application\DTOs;

class SriIvaTypeUpdateDTO
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $percentage = null,
        public readonly ?string $sriCode = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            percentage: $data['percentage'] ?? null,
            sriCode: $data['sri_code'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'percentage' => $this->percentage,
            'sri_code' => $this->sriCode,
        ], fn ($value): bool => $value !== null);
    }
}
