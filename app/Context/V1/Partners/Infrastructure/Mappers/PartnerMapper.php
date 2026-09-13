<?php

namespace App\Context\V1\Partners\Infrastructure\Mappers;

use App\Context\V1\Partners\Domain\Mappers\PartnerMapperInterface;
use App\Context\V1\Partners\Domain\Models\Partner;
use DateTimeInterface;

final class PartnerMapper implements PartnerMapperInterface
{
    public function toDomain(array $data): Partner
    {
        return new Partner(
            id: isset($data['id']) ? (int) $data['id'] : null,
            identification_type: $data['identification_type'] ?? null,
            identification_number: $data['identification_number'] ?? null,
            name: $data['name'] ?? null,
            last_name: $data['last_name'] ?? null,
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            address: $data['address'] ?? null,
            status: (bool) ($data['status'] ?? false),
            created_at: $this->date($data['created_at'] ?? null),
            updated_at: $this->date($data['updated_at'] ?? null),
            deleted_at: $this->date($data['deleted_at'] ?? null),
        );
    }

    public function toPersistence(Partner $partner): array
    {
        return [
            'identification_type' => $partner->identification_type,
            'identification_number' => $partner->identification_number,
            'name' => $partner->name,
            'last_name' => $partner->last_name,
            'email' => $partner->email,
            'phone' => $partner->phone,
            'address' => $partner->address,
            'status' => $partner->status,
        ];
    }

    public function toArray(Partner $partner): array
    {
        return [
            'id' => $partner->id,
            'identification_type' => $partner->identification_type,
            'identification_number' => $partner->identification_number,
            'name' => $partner->name,
            'last_name' => $partner->last_name,
            'email' => $partner->email,
            'phone' => $partner->phone,
            'address' => $partner->address,
            'status' => $partner->status,
            'created_at' => $partner->created_at,
            'updated_at' => $partner->updated_at,
            'deleted_at' => $partner->deleted_at,
        ];
    }

    private function date(mixed $value): ?string
    {
        return $value instanceof DateTimeInterface
            ? $value->format(DateTimeInterface::ATOM)
            : ($value === null ? null : (string) $value);
    }
}
