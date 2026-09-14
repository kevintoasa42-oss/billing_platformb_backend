<?php

namespace App\Context\V3\Modules\Authentication\Domain\Models;

/** A tenant and the active membership that grants a user access to it. */
final class AccessibleEnterprise
{
    public function __construct(
        public readonly string $id,
        public readonly ?int $legacyId,
        public readonly string $name,
        public readonly string $ruc,
        public readonly string $membershipId,
        public readonly int $authorizationVersion,
        public readonly array $capabilities,
    ) {}

    /** @return array{id: string, legacy_id: ?int, name: string, ruc: string} */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'legacy_id' => $this->legacyId,
            'name' => $this->name,
            'ruc' => $this->ruc,
        ];
    }
}
