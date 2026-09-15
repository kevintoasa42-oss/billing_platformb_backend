<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Application\DTOs;

final readonly class ThirdPartyFieldDefinitionCreateDTO
{
    /** @param array<string, mixed> $data */
    private function __construct(private array $data) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    /** @return array<string, mixed> */
    public function toDatabaseArray(): array
    {
        return [
            'code' => (string) $this->data['code'],
            'label' => (string) $this->data['label'],
            'scope' => (string) ($this->data['scope'] ?? 'both'),
            'data_type' => (string) ($this->data['data_type'] ?? 'text'),
            'validation' => is_array($this->data['validation'] ?? null) ? $this->data['validation'] : [],
            'sort_order' => (int) ($this->data['sort_order'] ?? 0),
            'is_required' => (bool) ($this->data['is_required'] ?? false),
            'is_active' => (bool) ($this->data['is_active'] ?? true),
        ];
    }
}
