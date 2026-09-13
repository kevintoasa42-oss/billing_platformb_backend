<?php

namespace App\Context\V1\Partners\Application\DTOs;

use App\Context\V1\Partners\Domain\Models\Partner;

final class PartnerDTO
{
    /** @param string[] $providedFields */
    public function __construct(
        public ?int $id = null,
        public ?string $identification_type = null,
        public ?string $identification_number = null,
        public ?string $name = null,
        public ?string $last_name = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $address = null,
        public bool $status = false,
        public ?string $created_at = null,
        public ?string $updated_at = null,
        public ?string $deleted_at = null,
        public array $providedFields = [],
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            identification_type: $data['identification_type'] ?? null,
            identification_number: $data['identification_number'] ?? null,
            name: $data['name'] ?? null,
            last_name: $data['last_name'] ?? null,
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            address: $data['address'] ?? null,
            status: array_key_exists('status', $data) ? (bool) $data['status'] : false,
            created_at: $data['created_at'] ?? null,
            updated_at: $data['updated_at'] ?? null,
            deleted_at: $data['deleted_at'] ?? null,
            providedFields: array_keys($data),
        );
    }

    public static function fromDomain(Partner $partner): self
    {
        return new self(
            id: $partner->id,
            identification_type: $partner->identification_type,
            identification_number: $partner->identification_number,
            name: $partner->name,
            last_name: $partner->last_name,
            email: $partner->email,
            phone: $partner->phone,
            address: $partner->address,
            status: $partner->status,
            created_at: $partner->created_at,
            updated_at: $partner->updated_at,
            deleted_at: $partner->deleted_at,
        );
    }

    /** @return array<string, mixed> */
    public function inputArray(): array
    {
        return array_intersect_key($this->toArray(), array_flip($this->providedFields));
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'identification_type' => $this->identification_type,
            'identification_number' => $this->identification_number,
            'name' => $this->name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
