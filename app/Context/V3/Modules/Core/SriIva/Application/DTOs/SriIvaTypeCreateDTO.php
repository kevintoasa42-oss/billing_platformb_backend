<?php

namespace App\Context\V3\Modules\Core\SriIva\Application\DTOs;

class SriIvaTypeCreateDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $percentage,
        public readonly string $sriCode,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            percentage: $data['percentage'],
            sriCode: $data['sri_code'],
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'percentage' => $this->percentage,
            'sri_code' => $this->sriCode,
        ];
    }
}
