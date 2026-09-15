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
        public readonly ?array $role = null,
        public readonly array $menus = [],
        public readonly array $permissions = [],
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $enterprises = array_map(static fn (AccessibleEnterprise $enterprise): array => $enterprise->toArray(), $this->enterprises);
        $user = $this->user->toArray();
        $user['platform_admin_enterprise_ids'] = $this->user->platformAdmin
            ? array_values(array_filter(array_column($enterprises, 'legacy_id')))
            : [];
        $permissions = $this->permissions === [] ? $this->enterprise->capabilities : $this->permissions;
        $role = $this->role ?? ($this->user->platformAdmin
            ? ['id' => 1, 'name' => 'Administrador consolidado', 'code' => 'CONSOLIDATED_ADMIN', 'is_system' => true, 'permissions' => $permissions]
            : (in_array('invoices.create', $permissions, true)
                ? ['id' => 2, 'name' => 'Facturador', 'code' => 'FACTURADOR', 'is_system' => true, 'permissions' => $permissions]
                : ['id' => 3, 'name' => 'Auditor', 'code' => 'AUDITOR', 'is_system' => true, 'permissions' => $permissions]));

        return [
            'user' => $user,
            'enterprise' => $this->enterprise->toArray(),
            'enterprises' => $enterprises,
            'platform_admin_enterprise_ids' => $user['platform_admin_enterprise_ids'],
            'role' => $role,
            'menus' => $this->menus,
            'permissions' => $permissions,
            'expires_at' => $this->expiresAt,
        ];
    }
}
