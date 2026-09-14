<?php

namespace App\Context\V3\Modules\Authentication\Application\DTOs;

final class CredentialsDTO
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            email: strtolower(trim((string) ($data['email'] ?? ''))),
            password: (string) ($data['password'] ?? ''),
        );
    }
}
