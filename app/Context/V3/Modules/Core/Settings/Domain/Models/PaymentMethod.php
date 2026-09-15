<?php

namespace App\Context\V3\Modules\Core\Settings\Domain\Models;

class PaymentMethod
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly ?string $alias = null,
        public readonly ?string $displayName = null,
        public readonly bool $requiresTerm = false,
        public readonly bool $isActive = true,
        public readonly bool $isDefault = false,
    ) {}

    public static function fromArray(array $data): self
    {
        $alias = $data['alias'] ?? null;
        $displayName = $data['display_name'] ?? ($alias ?: ($data['name'] ?? ''));

        return new self(
            code: (string) ($data['code'] ?? ''),
            name: (string) ($data['name'] ?? ''),
            alias: $alias,
            displayName: $displayName,
            requiresTerm: (bool) ($data['requires_term'] ?? false),
            isActive: (bool) ($data['is_active'] ?? true),
            isDefault: (bool) ($data['is_default'] ?? false),
        );
    }

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'alias' => $this->alias,
            'display_name' => $this->displayName ?: ($this->alias ?: $this->name),
            'requires_term' => $this->requiresTerm,
            'is_active' => $this->isActive,
            'is_default' => $this->isDefault,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function defaults(): array
    {
        return [
            [
                'code' => '01',
                'name' => 'Sin utilización del sistema financiero',
                'alias' => 'Efectivo',
                'display_name' => 'Efectivo',
                'requires_term' => false,
                'is_active' => true,
                'is_default' => true,
            ],
            [
                'code' => '19',
                'name' => 'Tarjeta de crédito',
                'alias' => 'Tarjeta de crédito',
                'display_name' => 'Tarjeta de crédito',
                'requires_term' => false,
                'is_active' => true,
                'is_default' => false,
            ],
        ];
    }
}
