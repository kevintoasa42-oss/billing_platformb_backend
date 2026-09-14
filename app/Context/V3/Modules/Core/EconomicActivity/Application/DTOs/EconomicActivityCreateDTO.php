<?php

namespace App\Context\V3\Modules\Core\EconomicActivity\Application\DTOs;

class EconomicActivityCreateDTO
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $id = null,
        public readonly string $catalogVersion = 'synthetic-lab-v1',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            id: $data['id'] ?? null,
            catalogVersion: $data['catalog_version'] ?? 'synthetic-lab-v1',
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'catalog_version' => $this->catalogVersion,
        ];
    }
}
