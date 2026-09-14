<?php

namespace App\Context\V3\Modules\Core\Carrier\Infrastructure\Mappers;

use App\Context\V3\Modules\Core\Carrier\Infrastructure\Laravel\Eloquent\Models\CarrierEstablishmentModel as CarrierEstablishmentEloquentModel;
use App\Context\V3\Modules\Core\Carrier\Domain\Models\CarrierEstablishment;

class CarrierEstablishmentMapper
{
    public function toDomain(CarrierEstablishmentEloquentModel $record): CarrierEstablishment
    {
        $activityIds = null;
        if ($record->relationLoaded('carrierCompany') && $record->carrierCompany !== null && $record->carrierCompany->relationLoaded('activities')) {
            $activityIds = $record->carrierCompany->activities->pluck('activity_id')->all();
        }

        return CarrierEstablishment::fromArray([
            'id' => $record->id,
            'tenant_id' => $record->tenant_id,
            'carrier_company_id' => $record->carrier_company_id,
            'sri_code' => $record->sri_code,
            'name' => $record->name,
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

    public function toDatabaseArray(CarrierEstablishment $establishment): array
    {
        $data = [
            'carrier_company_id' => $establishment->carrierCompanyId,
            'sri_code' => $establishment->sriCode,
            'name' => $establishment->name,
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

        return $data;
    }

    /**
     * Build the enriched response array with the nested carrier company
     * (including third-party info) and its activities.
     *
     * @return array<string, mixed>
     */
    public function toResponseArray(CarrierEstablishmentEloquentModel $record): array
    {
        $carrierCompany = null;
        if ($record->carrierCompany !== null) {
            $cc = $record->carrierCompany;
            $carrierCompany = [
                'id' => $cc->id,
                'legal_name' => $cc->legal_name,
                'trade_name' => $cc->trade_name,
                'is_active' => $cc->is_active,
                'third_party_name' => $cc->thirdParty?->name,
                'third_party_identification' => $cc->thirdParty?->identification,
            ];
        }

        $activities = [];
        if ($record->carrierCompany !== null && $record->carrierCompany->relationLoaded('activities')) {
            foreach ($record->carrierCompany->activities as $activity) {
                $activities[] = [
                    'activity_id' => $activity->activity_id,
                    'name' => $activity->economicActivity?->name,
                    'is_primary' => $activity->is_primary,
                ];
            }
        }

        return [
            'id' => $record->id,
            'tenant_id' => $record->tenant_id,
            'carrier_company_id' => $record->carrier_company_id,
            'carrier_company' => $carrierCompany,
            'sri_code' => $record->sri_code,
            'name' => $record->name,
            'address' => $record->address,
            'phone' => $record->phone,
            'email' => $record->email,
            'city_id' => $record->city_id,
            'is_active' => $record->is_active,
            'activities' => $activities,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function toResponseArrayList(iterable $records): array
    {
        $list = [];
        foreach ($records as $record) {
            $list[] = $this->toResponseArray($record);
        }

        return $list;
    }
}
