<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Domain\Models;

/**
 * Pure domain model for ThirdPartyRole.
 */
class ThirdPartyRole
{
    public function __construct(
        public readonly string $id,
        public readonly string $tenantId,
        public readonly string $thirdPartyId,
        public readonly string $role,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            tenantId: $data['tenant_id'],
            thirdPartyId: $data['third_party_id'],
            role: $data['role'],
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenantId,
            'third_party_id' => $this->thirdPartyId,
            'role' => $this->role,
        ];
    }
}
