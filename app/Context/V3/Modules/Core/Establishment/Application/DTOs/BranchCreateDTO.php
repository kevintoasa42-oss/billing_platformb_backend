<?php

namespace App\Context\V3\Modules\Core\Establishment\Application\DTOs;

class BranchCreateDTO
{
    /**
     * @param  array<string, mixed>|null  $issuancePoint
     */
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $sriEstablishmentNumber = null,
        public readonly ?string $branchCode = null,
        public readonly ?string $address = null,
        public readonly ?string $phone = null,
        public readonly ?string $email = null,
        public readonly ?int $cityId = null,
        public readonly ?array $issuancePoint = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            sriEstablishmentNumber: $data['sri_establishment_number'] ?? ($data['branch_code'] ?? null),
            branchCode: $data['branch_code'] ?? null,
            address: $data['address'] ?? null,
            phone: $data['phone'] ?? null,
            email: $data['email'] ?? null,
            cityId: isset($data['city_id']) ? (int) $data['city_id'] : null,
            issuancePoint: $data['issuance_point'] ?? null,
        );
    }

    public function sriCode(): string
    {
        $value = $this->sriEstablishmentNumber ?? $this->branchCode ?? '001';
        $digits = preg_replace('/\D+/', '', $value) ?: '001';

        return str_pad(substr($digits, -3), 3, '0', STR_PAD_LEFT);
    }
}
