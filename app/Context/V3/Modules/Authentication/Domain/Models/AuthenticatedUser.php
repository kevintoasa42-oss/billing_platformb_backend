<?php

namespace App\Context\V3\Modules\Authentication\Domain\Models;

/** Authentication identity, independent from Eloquent and Laravel guards. */
final class AuthenticatedUser
{
    public function __construct(
        public readonly string $id,
        public readonly ?int $legacyId,
        public readonly string $name,
        public readonly string $email,
        public readonly string $passwordHash,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly bool $platformAdmin,
        public readonly bool $active,
    ) {}

    /** @return array{id: string, legacy_id: ?int, name: string, email: string} */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'legacy_id' => $this->legacyId,
            'name' => $this->name,
            'email' => $this->email,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'platform_admin' => $this->platformAdmin,
            'active' => $this->active,
        ];
    }
}
