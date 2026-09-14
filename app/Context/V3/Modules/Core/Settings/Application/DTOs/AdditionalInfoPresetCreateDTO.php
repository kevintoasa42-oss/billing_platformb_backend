<?php

namespace App\Context\V3\Modules\Core\Settings\Application\DTOs;

class AdditionalInfoPresetCreateDTO
{
    /**
     * @param  array<int, mixed>|null  $accessRules
     */
    public function __construct(
        public readonly ?string $code = null,
        public readonly ?string $name = null,
        public readonly ?string $defaultValue = null,
        public readonly ?bool $autoApply = null,
        public readonly ?bool $valueEditable = null,
        public readonly ?bool $isRequired = null,
        public readonly ?bool $isActive = null,
        public readonly ?int $sortOrder = null,
        public readonly ?array $accessRules = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            code: $data['code'] ?? null,
            name: $data['name'] ?? null,
            defaultValue: $data['default_value'] ?? null,
            autoApply: array_key_exists('auto_apply', $data) ? (bool) $data['auto_apply'] : null,
            valueEditable: array_key_exists('value_editable', $data) ? (bool) $data['value_editable'] : null,
            isRequired: array_key_exists('is_required', $data) ? (bool) $data['is_required'] : null,
            isActive: array_key_exists('is_active', $data) ? (bool) $data['is_active'] : null,
            sortOrder: isset($data['sort_order']) ? (int) $data['sort_order'] : null,
            accessRules: $data['access_rules'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'default_value' => $this->defaultValue,
            'auto_apply' => $this->autoApply ?? false,
            'value_editable' => $this->valueEditable ?? true,
            'is_required' => $this->isRequired ?? false,
            'is_active' => $this->isActive ?? true,
            'sort_order' => $this->sortOrder ?? 0,
            'access_rules' => $this->accessRules ?? [],
        ];
    }
}
