<?php

namespace App\Context\V3\Modules\Core\EconomicActivity\Application\DTOs;

class EconomicActivityUpdateDTO
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $catalogVersion = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            catalogVersion: $data['catalog_version'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'catalog_version' => $this->catalogVersion,
        ], fn ($value) => $value !== null);
    }
}
