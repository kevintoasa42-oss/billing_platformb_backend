<?php

namespace App\Context\V3\Modules\Core\Establishment\Application\DTOs;

class EstablishmentUpdateDTO
{
    public function __construct(
        public readonly ?string $sriCode = null,
        public readonly ?string $name = null,
        public readonly ?string $branchCode = null,
        public readonly ?string $address = null,
        public readonly ?string $phone = null,
        public readonly ?string $email = null,
        public readonly ?int $cityId = null,
        public readonly ?bool $isActive = null,
        /** @var array<int, string>|null */
        public readonly ?array $activityIds = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            sriCode: $data['sri_code'] ?? null,
            name: $data['name'] ?? null,
            branchCode: $data['branch_code'] ?? null,
            address: $data['address'] ?? null,
            phone: $data['phone'] ?? null,
            email: $data['email'] ?? null,
            cityId: $data['city_id'] ?? null,
            isActive: $data['is_active'] ?? null,
            activityIds: $data['activity_ids'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = array_filter([
            'sri_code' => $this->sriCode,
            'name' => $this->name,
            'branch_code' => $this->branchCode,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'city_id' => $this->cityId,
            'is_active' => $this->isActive,
        ], fn ($value) => $value !== null);

        if ($this->activityIds !== null) {
            $data['activity_ids'] = $this->activityIds;
        }

        return $data;
    }
}
