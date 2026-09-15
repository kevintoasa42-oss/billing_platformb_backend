<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Application\DTOs;

final readonly class ThirdPartyFieldDefinitionUpdateDTO
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
        $attributes = [];

        foreach (['code', 'label', 'scope', 'data_type', 'validation', 'sort_order', 'is_required', 'is_active'] as $key) {
            if (array_key_exists($key, $this->data)) {
                $attributes[$key] = $this->data[$key];
            }
        }

        return $attributes;
    }
}
