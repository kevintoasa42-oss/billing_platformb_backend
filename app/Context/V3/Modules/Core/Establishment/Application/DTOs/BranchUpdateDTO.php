<?php

namespace App\Context\V3\Modules\Core\Establishment\Application\DTOs;

class BranchUpdateDTO
{
    /**
     * @param  array<string, mixed>|null  $issuancePoint
     */
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $branchCode = null,
        public readonly ?string $address = null,
        public readonly ?string $phone = null,
        public readonly ?string $email = null,
        public readonly ?int $cityId = null,
        public readonly ?bool $isActive = null,
        public readonly ?array $issuancePoint = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            branchCode: $data['branch_code'] ?? null,
            address: $data['address'] ?? null,
            phone: $data['phone'] ?? null,
            email: $data['email'] ?? null,
            cityId: isset($data['city_id']) ? (int) $data['city_id'] : null,
            isActive: array_key_exists('is_active', $data) ? (bool) $data['is_active'] : null,
            issuancePoint: $data['issuance_point'] ?? null,
        );
    }
}
