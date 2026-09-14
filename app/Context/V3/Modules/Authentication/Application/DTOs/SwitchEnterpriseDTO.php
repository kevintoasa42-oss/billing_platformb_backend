<?php

namespace App\Context\V3\Modules\Authentication\Application\DTOs;

final class SwitchEnterpriseDTO
{
    public function __construct(public readonly string $enterpriseId) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self((string) ($data['enterprise_id'] ?? ''));
    }
}
