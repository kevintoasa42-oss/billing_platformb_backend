<?php

namespace App\Context\V3\Modules\Core\Establishment\Application\DTOs;

class IssuancePointCreateDTO
{
    /**
     * @param  array<int, array{document_type: string, last_number: int}>|null  $initialSequences
     */
    public function __construct(
        public readonly ?string $issuancePointNumber = null,
        public readonly ?string $name = null,
        public readonly ?bool $isActive = null,
        public readonly ?bool $isDefault = null,
        public readonly ?bool $hasTaxValidity = null,
        public readonly ?array $initialSequences = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            issuancePointNumber: $data['issuance_point_number'] ?? null,
            name: $data['name'] ?? null,
            isActive: array_key_exists('is_active', $data) ? (bool) $data['is_active'] : null,
            isDefault: array_key_exists('is_default', $data) ? (bool) $data['is_default'] : null,
            hasTaxValidity: array_key_exists('has_tax_validity', $data) ? (bool) $data['has_tax_validity'] : null,
            initialSequences: $data['initial_sequences'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'issuance_point_number' => $this->issuancePointNumber ?? '001',
            'name' => $this->name,
            'is_active' => $this->isActive ?? true,
            'is_default' => $this->isDefault ?? false,
            'has_tax_validity' => $this->hasTaxValidity ?? true,
            'initial_sequences' => $this->initialSequences,
        ];
    }
}
