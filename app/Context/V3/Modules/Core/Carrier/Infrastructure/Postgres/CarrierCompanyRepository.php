<?php

namespace App\Context\V3\Modules\Core\Carrier\Infrastructure\Postgres;

use App\Context\V3\Modules\Core\Carrier\Infrastructure\Laravel\Eloquent\Models\CarrierCompanyModel as CarrierCompanyEloquentModel;
use App\Context\V3\Modules\Core\Carrier\Domain\Models\CarrierCompany;
use App\Context\V3\Modules\Core\Carrier\Domain\Repository\CarrierCompanyRepositoryInterface;
use App\Context\V3\Modules\Core\Carrier\Infrastructure\Mappers\CarrierCompanyMapper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CarrierCompanyRepository implements CarrierCompanyRepositoryInterface
{
    public function __construct(
        private readonly CarrierCompanyMapper $mapper,
    ) {}

    public function create(CarrierCompany $company): CarrierCompany
    {
        return DB::connection('master_v3')->transaction(function () use ($company): CarrierCompany {
            $record = CarrierCompanyEloquentModel::query()->firstOrCreate(
                [
                    'tenant_id' => $company->tenantId,
                    'third_party_id' => $company->thirdPartyId,
                ],
                array_merge($this->mapper->toDatabaseArray($company), [
                    'id' => $company->id ?? Str::uuid()->toString(),
                ])
            );

            if (! $record->wasRecentlyCreated) {
                $record->update([
                    'legal_name' => $company->legalName,
                    'trade_name' => $company->tradeName,
                    'is_active' => $company->isActive ?? true,
                ]);
                $record = $record->fresh();
            }

            return $this->mapper->toDomain($record);
        });
    }

    public function findByThirdPartyId(string $thirdPartyId): ?CarrierCompany
    {
        $record = CarrierCompanyEloquentModel::query()
            ->where('third_party_id', $thirdPartyId)
            ->first();

        return $record !== null ? $this->mapper->toDomain($record) : null;
    }
}
