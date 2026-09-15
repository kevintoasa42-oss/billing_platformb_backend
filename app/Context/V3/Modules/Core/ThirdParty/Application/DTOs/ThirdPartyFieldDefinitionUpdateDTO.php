<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Application\DTOs;

final class ThirdPartyFieldDefinitionUpdateDTO
{
    public function __construct(
        public readonly ?string $code = null,
        public readonly ?string $label = null,
        public readonly ?string $scope = null,
        public readonly ?string $dataType = null,
        /** @var array<string, mixed>|null */
        public readonly ?array $validation = null,
        public readonly ?int $sortOrder = null,
        public readonly ?bool $isRequired = null,
        public readonly ?bool $isActive = null,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            code: isset($data['code']) ? (string) $data['code'] : null,
            label: isset($data['label']) ? (string) $data['label'] : null,
            scope: isset($data['scope']) ? (string) $data['scope'] : null,
            dataType: isset($data['data_type']) ? (string) $data['data_type'] : null,
            validation: is_array($data['validation'] ?? null) ? $data['validation'] : null,
            sortOrder: isset($data['sort_order']) ? (int) $data['sort_order'] : null,
            isRequired: array_key_exists('is_required', $data) ? (bool) $data['is_required'] : null,
            isActive: array_key_exists('is_active', $data) ? (bool) $data['is_active'] : null,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'code' => $this->code,
            'label' => $this->label,
            'scope' => $this->scope,
            'data_type' => $this->dataType,
            'validation' => $this->validation,
            'sort_order' => $this->sortOrder,
            'is_required' => $this->isRequired,
            'is_active' => $this->isActive,
        ], fn ($value) => $value !== null);
    }
}
