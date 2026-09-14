<?php

namespace App\Context\V3\Modules\Core\Company\Infrastructure\Mappers;

use App\Context\V3\Modules\Core\Company\Domain\Models\Company;
use App\Context\V3\Modules\Core\Company\Infrastructure\Laravel\Eloquent\Models\CompanyModel;

class CompanyMapper
{
    public function toDomain(CompanyModel $record): Company
    {
        $activityIds = null;
        if ($record->relationLoaded('activities')) {
            $activityIds = $record->activities->pluck('activity_id')->all();
        }

        return Company::fromArray([
            'id' => $record->id,
            'tenant_id' => $record->tenant_id,
            'name' => $record->name,
            'ruc' => $record->ruc,
            'legal_name' => $record->legal_name,
            'trade_name' => $record->trade_name,
            'matrix_address' => $record->matrix_address,
            'operations_start_date' => $record->operations_start_date?->toDateString(),
            'city_id' => $record->city_id,
            'phone' => $record->phone,
            'corporate_email' => $record->corporate_email,
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

    public function toDatabaseArray(Company $company): array
    {
        $data = [
            'name' => $company->name,
            'ruc' => $company->ruc,
            'legal_name' => $company->legalName,
            'trade_name' => $company->tradeName,
            'matrix_address' => $company->matrixAddress,
            'operations_start_date' => $company->operationsStartDate,
            'city_id' => $company->cityId,
            'phone' => $company->phone,
            'corporate_email' => $company->corporateEmail,
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
