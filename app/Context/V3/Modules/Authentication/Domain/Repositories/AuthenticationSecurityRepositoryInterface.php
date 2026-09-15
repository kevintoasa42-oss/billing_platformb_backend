<?php

namespace App\Context\V3\Modules\Authentication\Domain\Repositories;

use App\Context\V3\Modules\Authentication\Domain\Models\AuthenticationSession;

interface AuthenticationSecurityRepositoryInterface
{
    /** @return list<array<string, mixed>> */
    public function activeSessions(AuthenticationSession $current): array;

    public function revokeSession(AuthenticationSession $current, int $sessionLegacyId): bool;

    public function revokeOtherSessions(AuthenticationSession $current): int;

    public function verifyPassword(string $userId, string $password): bool;

    public function changePassword(string $userId, string $newPassword): void;

    /** @return array{enabled: bool, confirmed_at: ?string} */
    public function mfaStatus(string $userId): array;

    /** @return array{url: string, barcode: string} */
    public function beginMfa(string $userId): array;

    /** @return array{enabled: bool, confirmed_at: ?string} */
    public function confirmMfa(string $userId, string $code): array;

    /** @return array<string, mixed> */
    public function preferences(string $userId): array;

    /** @param array<string, mixed> $preferences */
    public function savePreferences(string $userId, array $preferences): array;

    public function hasActiveUserWithLegacyId(int $legacyId): bool;
}
