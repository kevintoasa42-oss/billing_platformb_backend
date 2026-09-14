<?php

namespace App\Context\V3\Modules\Core\Settings\Application\DTOs;

class CustomerSettingsDTO
{
    public function __construct(
        public readonly ?bool $allowMultiplePlates = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            allowMultiplePlates: array_key_exists('allow_multiple_plates', $data) ? (bool) $data['allow_multiple_plates'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'allow_multiple_plates' => $this->allowMultiplePlates,
        ], fn ($value): bool => $value !== null);
    }
}
