<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Domain\Models;

/**
 * Pure domain model for ThirdPartyActivity.
 */
class ThirdPartyActivity
{
    public function __construct(
        public readonly string $id,
        public readonly string $tenantId,
        public readonly string $thirdPartyId,
        public readonly ?string $establishmentCode = null,
        public readonly ?string $activityId = null,
        public readonly ?string $validity = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            tenantId: $data['tenant_id'],
            thirdPartyId: $data['third_party_id'],
            establishmentCode: $data['establishment_code'] ?? null,
            activityId: $data['activity_id'] ?? null,
            validity: $data['validity'] ?? null,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenantId,
            'third_party_id' => $this->thirdPartyId,
            'establishment_code' => $this->establishmentCode,
            'activity_id' => $this->activityId,
            'validity' => $this->validity,
        ];
    }
}
