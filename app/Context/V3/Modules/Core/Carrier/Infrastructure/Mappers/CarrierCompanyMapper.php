<?php

namespace App\Context\V3\Modules\Core\Carrier\Infrastructure\Mappers;

use App\Context\V3\Modules\Core\Carrier\Infrastructure\Laravel\Eloquent\Models\CarrierCompanyModel as CarrierCompanyEloquentModel;
use App\Context\V3\Modules\Core\Carrier\Domain\Models\CarrierCompany;

class CarrierCompanyMapper
{
    public function toDomain(CarrierCompanyEloquentModel $record): CarrierCompany
    {
        return CarrierCompany::fromArray([
            'id' => $record->id,
            'tenant_id' => $record->tenant_id,
            'third_party_id' => $record->third_party_id,
            'legal_name' => $record->legal_name,
            'trade_name' => $record->trade_name,
            'is_active' => $record->is_active,
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

    public function toDatabaseArray(CarrierCompany $company): array
    {
        $data = [
            'third_party_id' => $company->thirdPartyId,
            'legal_name' => $company->legalName,
            'trade_name' => $company->tradeName,
            'is_active' => $company->isActive,
        ];

        if ($company->id !== null) {
            $data['id'] = $company->id;
        }

        if ($company->tenantId !== null) {
            $data['tenant_id'] = $company->tenantId;
        }

        return $data;
    }
}
