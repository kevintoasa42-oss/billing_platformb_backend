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

    /** @return array<string, int|null|string> */
    public function toArray(): array
    {
        return [
            'id' => $this->legacyId ?? $this->id,
            'uuid' => $this->id,
            'legacy_id' => $this->legacyId,
            'name' => $this->name,
            'legal_name' => $this->name,
            'trade_name' => $this->name,
            'short_name' => $this->name,
            'ruc' => $this->ruc,
            'matrix_address' => null,
            'operations_start_date' => null,
            'city_id' => null,
            'phone' => null,
            'corporate_email' => null,
        ];
    }
}
