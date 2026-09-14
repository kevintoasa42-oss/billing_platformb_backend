<?php

namespace App\Context\V3\Modules\Authentication\Application\DTOs;

use App\Context\V3\Modules\Authentication\Domain\Models\AccessibleEnterprise;
use App\Context\V3\Modules\Authentication\Domain\Models\AuthenticatedUser;

final class LoginChallengeDTO
{
    /** @param AccessibleEnterprise[] $enterprises */
    public function __construct(
        public readonly AuthenticatedUser $user,
        public readonly array $enterprises,
        public readonly int $expiresAt,
    ) {}

    /** @return array{user_id: string, enterprise_ids: string[], expires_at: int} */
    public function cookiePayload(): array
    {
        return [
            'user_id' => $this->user->id,
            'enterprise_ids' => array_map(static fn (AccessibleEnterprise $enterprise): string => $enterprise->id, $this->enterprises),
            'expires_at' => $this->expiresAt,
        ];
    }

    /** @return array{user: array<string, mixed>, enterprises: array<int, array<string, mixed>>} */
    public function toArray(): array
    {
        return [
            'user' => $this->user->toArray(),
            'enterprises' => array_map(static fn (AccessibleEnterprise $enterprise): array => $enterprise->toArray(), $this->enterprises),
        ];
    }
}
