<?php

namespace App\Context\V3\Modules\Authentication\Application\DTOs;

use App\Context\V3\Modules\Authentication\Domain\Models\AccessibleEnterprise;
use App\Context\V3\Modules\Authentication\Domain\Models\AuthenticatedUser;

final class AuthenticationSessionDTO
{
    /** @param AccessibleEnterprise[] $enterprises */
    public function __construct(
        public readonly ?string $token,
        public readonly AuthenticatedUser $user,
        public readonly AccessibleEnterprise $enterprise,
        public readonly ?string $expiresAt,
        public readonly array $enterprises,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $enterprises = array_map(static fn (AccessibleEnterprise $enterprise): array => $enterprise->toArray(), $this->enterprises);
        $user = $this->user->toArray();
        $user['platform_admin_enterprise_ids'] = $this->user->platformAdmin
            ? array_values(array_filter(array_column($enterprises, 'legacy_id')))
            : [];

        return [
            'user' => $user,
            'enterprise' => $this->enterprise->toArray(),
            'enterprises' => $enterprises,
            'platform_admin_enterprise_ids' => $user['platform_admin_enterprise_ids'],
            'expires_at' => $this->expiresAt,
        ];
    }
}
