<?php

namespace App\Context\V3\Modules\Authentication\Infrastructure\Mappers;

use App\Context\V3\Modules\Authentication\Domain\Mappers\AuthenticationMapperInterface;
use App\Context\V3\Modules\Authentication\Domain\Models\AccessibleEnterprise;
use App\Context\V3\Modules\Authentication\Domain\Models\AuthenticatedUser;
use App\Context\V3\Modules\Authentication\Domain\Models\AuthenticationSession;

final class AuthenticationMapper implements AuthenticationMapperInterface
{
    public function user(array $data): AuthenticatedUser
    {
        return new AuthenticatedUser(
            id: (string) ($data['id'] ?? ''),
            legacyId: isset($data['legacy_id']) ? (int) $data['legacy_id'] : null,
            name: (string) ($data['name'] ?? ''),
            email: (string) ($data['email'] ?? ''),
            passwordHash: (string) ($data['password_hash'] ?? ''),
            firstName: (string) ($data['first_name'] ?? ''),
            lastName: (string) ($data['last_name'] ?? ''),
            platformAdmin: (bool) ($data['platform_admin'] ?? false),
            active: (bool) ($data['active'] ?? false),
        );
    }

    public function enterprise(array $data): AccessibleEnterprise
    {
        return new AccessibleEnterprise(
            id: (string) ($data['id'] ?? ''),
            legacyId: isset($data['legacy_id']) ? (int) $data['legacy_id'] : null,
            name: (string) ($data['name'] ?? ''),
            ruc: (string) ($data['ruc'] ?? ''),
            membershipId: (string) ($data['membership_id'] ?? ''),
            authorizationVersion: (int) ($data['authorization_version'] ?? 0),
            capabilities: $this->capabilities($data['capabilities'] ?? []),
        );
    }

    public function session(array $data): AuthenticationSession
    {
        return new AuthenticationSession(
            tokenHash: (string) ($data['token_hash'] ?? ''),
            tenantId: (string) ($data['tenant_id'] ?? ''),
            userId: (string) ($data['user_id'] ?? ''),
            membershipId: isset($data['membership_id']) ? (string) $data['membership_id'] : null,
            authorizationVersion: isset($data['authorization_version']) ? (int) $data['authorization_version'] : null,
            expiresAt: isset($data['expires_at']) ? (string) $data['expires_at'] : null,
            capabilities: $this->capabilities($data['capabilities'] ?? []),
            platformAdmin: (bool) ($data['platform_admin'] ?? false),
        );
    }

    /** @return string[] */
    private function capabilities(mixed $capabilities): array
    {
        if (is_array($capabilities)) {
            return array_values(array_map('strval', $capabilities));
        }

        $value = trim((string) $capabilities, '{}');

        return $value === '' ? [] : array_values(array_map('trim', str_getcsv($value)));
    }
}
