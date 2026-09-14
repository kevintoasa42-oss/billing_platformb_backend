<?php

namespace App\Context\V3\Modules\Authentication\Application\DTOs;

use App\Context\V3\Modules\Authentication\Domain\Models\AccessibleEnterprise;
use App\Context\V3\Modules\Authentication\Domain\Models\AuthenticatedUser;

final class AuthenticationSessionDTO
{
    public function __construct(
        public readonly ?string $token,
        public readonly AuthenticatedUser $user,
        public readonly AccessibleEnterprise $enterprise,
        public readonly ?string $expiresAt,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'user' => $this->user->toArray(),
            'enterprise' => $this->enterprise->toArray(),
            'expires_at' => $this->expiresAt,
        ];
    }
}
