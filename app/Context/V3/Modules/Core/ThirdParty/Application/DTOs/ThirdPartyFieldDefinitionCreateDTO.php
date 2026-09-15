<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Application\DTOs;

final class ThirdPartyFieldDefinitionCreateDTO
{
    public function __construct(
        public readonly string $code,
        public readonly string $label,
        public readonly string $scope,
        public readonly string $dataType,
        /** @var array<string, mixed> */
        public readonly array $validation,
        public readonly int $sortOrder,
        public readonly bool $isRequired,
        public readonly bool $isActive,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            code: (string) ($data['code'] ?? ''),
            label: (string) ($data['label'] ?? ''),
            scope: (string) ($data['scope'] ?? 'both'),
            dataType: (string) ($data['data_type'] ?? 'text'),
            validation: is_array($data['validation'] ?? null) ? $data['validation'] : [],
            sortOrder: (int) ($data['sort_order'] ?? 0),
            isRequired: (bool) ($data['is_required'] ?? false),
            isActive: (bool) ($data['is_active'] ?? true),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'label' => $this->label,
            'scope' => $this->scope,
            'data_type' => $this->dataType,
            'validation' => $this->validation,
            'sort_order' => $this->sortOrder,
            'is_required' => $this->isRequired,
            'is_active' => $this->isActive,
        ];
    }
}
