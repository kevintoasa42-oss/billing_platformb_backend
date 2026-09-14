<?php

namespace App\Context\V3\Modules\Authentication\Domain\Models;

/** Resolved server-side session bound to exactly one V3 tenant. */
final class AuthenticationSession
{
    /** @param string[] $capabilities */
    public function __construct(
        public readonly string $tokenHash,
        public readonly string $tenantId,
        public readonly string $userId,
        public readonly ?string $membershipId,
        public readonly ?int $authorizationVersion,
        public readonly ?string $expiresAt,
        public readonly array $capabilities = [],
        public readonly bool $platformAdmin = false,
    ) {}
}
