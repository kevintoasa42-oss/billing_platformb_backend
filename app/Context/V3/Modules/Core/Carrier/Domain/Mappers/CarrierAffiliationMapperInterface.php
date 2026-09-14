<?php

namespace App\Context\V3\Modules\Core\Carrier\Domain\Mappers;

use App\Context\V3\Modules\Core\Carrier\Infrastructure\Laravel\Eloquent\Models\CarrierAffiliationModel as CarrierAffiliationEloquentModel;
use App\Context\V3\Modules\Core\Carrier\Domain\Models\CarrierAffiliation;

interface CarrierAffiliationMapperInterface
{
    public function toDomain(CarrierAffiliationEloquentModel $record): CarrierAffiliation;

    /**
     * @param  iterable<CarrierAffiliationEloquentModel>  $records
     * @return array<int, CarrierAffiliation>
     */
    public function toDomainList(iterable $records): array;

    /**
     * @return array<string, mixed>
     */
    public function toDatabaseArray(CarrierAffiliation $affiliation): array;
}
