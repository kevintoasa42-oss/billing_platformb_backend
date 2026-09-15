<?php

namespace App\Context\V3\Modules\Core\Establishment\Application\DTOs;

class IssuancePointUpdateDTO
{
    /**
     * @param  array<int, array{document_type: string, last_number: int}>|null  $sequences
     */
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $issuancePointNumber = null,
        public readonly ?bool $isActive = null,
        public readonly ?bool $isDefault = null,
        public readonly ?bool $hasTaxValidity = null,
        public readonly ?array $sequences = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            issuancePointNumber: $data['issuance_point_number'] ?? null,
            isActive: array_key_exists('is_active', $data) ? (bool) $data['is_active'] : null,
            isDefault: array_key_exists('is_default', $data) ? (bool) $data['is_default'] : null,
            hasTaxValidity: array_key_exists('has_tax_validity', $data) ? (bool) $data['has_tax_validity'] : null,
            sequences: $data['sequences'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'issuance_point_number' => $this->issuancePointNumber,
            'is_active' => $this->isActive,
            'is_default' => $this->isDefault,
            'has_tax_validity' => $this->hasTaxValidity,
            'sequences' => $this->sequences,
        ], fn ($value): bool => $value !== null);
    }
}
