<?php

namespace App\Context\V3\Modules\Authentication\Domain\Repositories;

use App\Context\V3\Modules\Authentication\Domain\Models\AccessibleEnterprise;
use App\Context\V3\Modules\Authentication\Domain\Models\AuthenticatedUser;
use App\Context\V3\Modules\Authentication\Domain\Models\AuthenticationSession;

interface AuthenticationRepositoryInterface
{
    public function findUserByEmail(string $email): ?AuthenticatedUser;

    public function findUserById(string $id): ?AuthenticatedUser;

    /** @return AccessibleEnterprise[] */
    public function enterprisesForUser(string $userId): array;

    public function enterpriseForUser(string $userId, string $enterpriseId): ?AccessibleEnterprise;

    public function createSession(
        AccessibleEnterprise $enterprise,
        string $userId,
        string $tokenHash,
        int $ttlMinutes,
    ): AuthenticationSession;

    public function resolveSession(string $tokenHash): ?AuthenticationSession;

    public function revokeSession(string $tokenHash): void;

    /** @return list<array<string, mixed>> */
    public function menuTreeForTenant(string $tenantId): array;
}
