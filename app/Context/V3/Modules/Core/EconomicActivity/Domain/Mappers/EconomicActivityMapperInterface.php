<?php

namespace App\Context\V3\Modules\Core\EconomicActivity\Domain\Mappers;

use App\Context\V3\Modules\Core\EconomicActivity\Domain\Models\EconomicActivity;
use App\Context\V3\Modules\Core\EconomicActivity\Infrastructure\Laravel\Eloquent\Models\EconomicActivityModel;

interface EconomicActivityMapperInterface
{
    public function toDomain(EconomicActivityModel $record): EconomicActivity;

    /**
     * @param  iterable<EconomicActivityModel>  $records
     * @return array<int, EconomicActivity>
     */
    public function toDomainList(iterable $records): array;

    /**
     * @return array<string, mixed>
     */
    public function toDatabaseArray(EconomicActivity $activity): array;
}
