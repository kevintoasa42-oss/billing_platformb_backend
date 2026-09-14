<?php

namespace App\Context\V3\Modules\Core\Establishment\Infrastructure\Mappers;

use App\Context\V3\Modules\Core\Establishment\Domain\Models\Establishment;
use App\Context\V3\Modules\Core\Establishment\Infrastructure\Laravel\Eloquent\Models\EstablishmentModel;

class EstablishmentMapper
{
    public function toDomain(EstablishmentModel $record): Establishment
    {
        $activityIds = null;
        if ($record->relationLoaded('activities')) {
            $activityIds = $record->activities->pluck('activity_id')->all();
        }

        return Establishment::fromArray([
            'id' => $record->id,
            'tenant_id' => $record->tenant_id,
            'company_id' => $record->company_id,
            'sri_code' => $record->sri_code,
            'name' => $record->name,
            'legacy_id' => $record->legacy_id,
            'branch_code' => $record->branch_code,
            'address' => $record->address,
            'phone' => $record->phone,
            'email' => $record->email,
            'city_id' => $record->city_id,
            'is_active' => $record->is_active,
            'activity_ids' => $activityIds,
        ]);
    }

    public function toDomainList(iterable $records): array
    {
        $list = [];
        foreach ($records as $record) {
            $list[] = $this->toDomain($record);
        }

        return $list;
    }

    public function toDatabaseArray(Establishment $establishment): array
    {
        $data = [
            'company_id' => $establishment->companyId,
            'sri_code' => $establishment->sriCode,
            'name' => $establishment->name,
            'branch_code' => $establishment->branchCode,
            'address' => $establishment->address,
            'phone' => $establishment->phone,
            'email' => $establishment->email,
            'city_id' => $establishment->cityId,
            'is_active' => $establishment->isActive ?? true,
        ];

        if ($establishment->id !== null) {
            $data['id'] = $establishment->id;
        }

        if ($establishment->tenantId !== null) {
            $data['tenant_id'] = $establishment->tenantId;
        }

        if ($establishment->legacyId !== null) {
            $data['legacy_id'] = $establishment->legacyId;
        }

        return $data;
    }
}
