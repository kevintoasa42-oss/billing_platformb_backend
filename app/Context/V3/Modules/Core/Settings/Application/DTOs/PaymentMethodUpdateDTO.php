<?php

namespace App\Context\V3\Modules\Core\Settings\Application\DTOs;

class PaymentMethodUpdateDTO
{
    public function __construct(
        public readonly ?string $alias = null,
        public readonly ?bool $isActive = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            alias: $data['alias'] ?? null,
            isActive: array_key_exists('is_active', $data) ? (bool) $data['is_active'] : null,
        );
    }
}
