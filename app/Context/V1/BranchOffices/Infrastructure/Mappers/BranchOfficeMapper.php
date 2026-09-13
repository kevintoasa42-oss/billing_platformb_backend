<?php

namespace App\Context\V1\BranchOffices\Infrastructure\Mappers;

use App\Context\V1\BranchOffices\Domain\Mappers\BranchOfficeMapperInterface;
use App\Context\V1\BranchOffices\Domain\Models\BranchOffice;
use DateTimeInterface;

final class BranchOfficeMapper implements BranchOfficeMapperInterface
{
    public function toDomain(array $data): BranchOffice
    {
        return new BranchOffice(
            id: isset($data['id']) ? (int)$data['id'] : null,
            name: $data['name'] ?? null,
            code_sri: $data['code_sri'] ?? null,
            status: (bool)($data['status'] ?? false),
            type: $data['type'] ?? null,
            default: (bool)($data['default'] ?? false),
            created_at: $this->date($data['created_at'] ?? null),
            updated_at: $this->date($data['updated_at'] ?? null),
            deleted_at: $this->date($data['deleted_at'] ?? null),
        );
    }

    private function date(mixed $value): ?string
    {
        return $value instanceof DateTimeInterface ? $value->format(DateTimeInterface::ATOM) : ($value === null ? null : (string)$value);
    }

    public function toPersistence(BranchOffice $branchOffice): array
    {
        return [
            'name' => $branchOffice->name, 'code_sri' => $branchOffice->code_sri,
            'status' => $branchOffice->status, 'type' => $branchOffice->type,
            'default' => $branchOffice->default,
        ];
    }

    public function toArray(BranchOffice $branchOffice): array
    {
        return [
            'id' => $branchOffice->id, 'name' => $branchOffice->name, 'code_sri' => $branchOffice->code_sri,
            'status' => $branchOffice->status, 'type' => $branchOffice->type, 'default' => $branchOffice->default,
            'created_at' => $branchOffice->created_at, 'updated_at' => $branchOffice->updated_at,
            'deleted_at' => $branchOffice->deleted_at,
        ];
    }
}
