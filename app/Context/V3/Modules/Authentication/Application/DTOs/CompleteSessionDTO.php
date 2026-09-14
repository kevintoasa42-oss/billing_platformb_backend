<?php

namespace App\Context\V3\Modules\Authentication\Application\DTOs;

final class CompleteSessionDTO
{
    /** @param string[] $allowedEnterpriseIds */
    public function __construct(
        public readonly string $userId,
        public readonly array $allowedEnterpriseIds,
        public readonly int $expiresAt,
        public readonly string $enterpriseId,
    ) {}

    /** @param array<string, mixed> $challenge */
    public static function fromChallenge(array $challenge, string $enterpriseId): self
    {
        return new self(
            userId: (string) ($challenge['user_id'] ?? ''),
            allowedEnterpriseIds: array_values(array_map('strval', (array) ($challenge['enterprise_ids'] ?? []))),
            expiresAt: (int) ($challenge['expires_at'] ?? 0),
            enterpriseId: $enterpriseId,
        );
    }
}
