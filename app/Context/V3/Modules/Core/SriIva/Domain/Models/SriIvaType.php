<?php

namespace App\Context\V3\Modules\Core\SriIva\Domain\Models;

class SriIvaType
{
    /**
     * @param  SriIvaPercentage[]  $percentages
     */
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $percentage,
        public readonly string $sriCode,
        public readonly bool $isActive,
        public readonly array $percentages = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            name: $data['name'],
            percentage: (string) $data['percentage'],
            sriCode: $data['sri_code'],
            isActive: (bool) $data['is_active'],
            percentages: $data['percentages'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'percentage' => $this->percentage,
            'sri_code' => $this->sriCode,
            'is_active' => $this->isActive,
            'percentages' => array_map(
                static fn (SriIvaPercentage $p): array => $p->toArray(),
                $this->percentages,
            ),
        ];
    }
}
