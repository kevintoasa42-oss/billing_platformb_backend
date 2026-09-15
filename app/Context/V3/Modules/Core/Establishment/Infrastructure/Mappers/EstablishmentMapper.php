<?php

namespace App\Context\V3\Modules\Core\Establishment\Infrastructure\Mappers;

use App\Context\V3\Modules\Core\Establishment\Domain\Models\Establishment;
use App\Context\V3\Modules\Core\Establishment\Infrastructure\Laravel\Eloquent\Models\EstablishmentModel;
use App\Context\V3\Modules\Fiscal\Infrastructure\Laravel\Eloquent\Models\SequenceModel;

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

    /**
     * Build Establishment with nested issuance_points (branch shape).
     *
     * Requires the `emissionPoints` relation to be eager-loaded on the model.
     */
    public function toBranchDomain(EstablishmentModel $record): Establishment
    {
        $points = [];
        if ($record->relationLoaded('emissionPoints')) {
            $points = $record->emissionPoints
                ->sortBy('legacy_id')
                ->map(function ($point) use ($record): array {
                    $last = (int) (SequenceModel::query()
                        ->where('emission_point_id', $point->id)
                        ->where('document_type', 'invoice')
                        ->value('last_number') ?? 0);

                    return [
                        'id' => (int) $point->legacy_id,
                        'branch_id' => (int) $record->legacy_id,
                        'name' => trim((string) ($point->name ?? '')) !== '' ? $point->name : 'Punto '.$point->sri_code,
                        'issuance_point_number' => (string) $point->sri_code,
                        'is_active' => (bool) $point->is_active,
                        'is_default' => (bool) $point->is_default,
                        'has_tax_validity' => (bool) $point->has_tax_validity,
                        'last_issued_sequential' => $last,
                        'next_sequential' => $last + 1,
                    ];
                })
                ->values()
                ->all();
        }

        return new Establishment(
            id: (string) $record->id,
            tenantId: (string) $record->tenant_id,
            companyId: (string) $record->company_id,
            sriCode: (string) $record->sri_code,
            name: $record->name,
            legacyId: (int) $record->legacy_id,
            branchCode: $record->branch_code ?? $record->sri_code,
            address: $record->address,
            phone: $record->phone,
            email: $record->email,
            cityId: $record->city_id !== null ? (int) $record->city_id : null,
            isActive: (bool) $record->is_active,
            issuancePoints: $points,
        );
    }

    public function toDomainList(iterable $records): array
    {
        $list = [];
        foreach ($records as $record) {
            $list[] = $this->toDomain($record);
        }

        return $list;
    }

    /**
     * @param  iterable<int, EstablishmentModel>  $records
     * @return array<int, Establishment>
     */
    public function toBranchDomainList(iterable $records): array
    {
        $list = [];
        foreach ($records as $record) {
            $list[] = $this->toBranchDomain($record);
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
