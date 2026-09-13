<?php

namespace App\Context\V1\EmissionPoints\Infrastructure\Mappers;

use App\Context\V1\EmissionPoints\Domain\Mappers\EmissionPointMapperInterface;
use App\Context\V1\EmissionPoints\Domain\Models\EmissionPoint;
use DateTimeInterface;

final class EmissionPointMapper implements EmissionPointMapperInterface
{
    public function toDomain(array $data): EmissionPoint
    {
        return new EmissionPoint(
            id: isset($data['id']) ? (int) $data['id'] : null,
            branch_office_id: isset($data['branch_office_id']) ? (int) $data['branch_office_id'] : null,
            name: $data['name'] ?? null, emission_point: $data['emission_point'] ?? null,
            status: (bool) ($data['status'] ?? false), default: (bool) ($data['default'] ?? false),
            created_at: $this->date($data['created_at'] ?? null), updated_at: $this->date($data['updated_at'] ?? null),
            deleted_at: $this->date($data['deleted_at'] ?? null),
        );
    }

    public function toPersistence(EmissionPoint $emissionPoint): array
    {
        return [
            'branch_office_id' => $emissionPoint->branch_office_id,
            'name' => $emissionPoint->name, 'emission_point' => $emissionPoint->emission_point,
            'status' => $emissionPoint->status, 'default' => $emissionPoint->default,
        ];
    }

    public function toArray(EmissionPoint $emissionPoint): array
    {
        return [
            'id' => $emissionPoint->id, 'branch_office_id' => $emissionPoint->branch_office_id,
            'name' => $emissionPoint->name, 'emission_point' => $emissionPoint->emission_point,
            'status' => $emissionPoint->status, 'default' => $emissionPoint->default,
            'created_at' => $emissionPoint->created_at, 'updated_at' => $emissionPoint->updated_at,
            'deleted_at' => $emissionPoint->deleted_at,
        ];
    }

    private function date(mixed $value): ?string
    {
        return $value instanceof DateTimeInterface ? $value->format(DateTimeInterface::ATOM) : ($value === null ? null : (string) $value);
    }
}
