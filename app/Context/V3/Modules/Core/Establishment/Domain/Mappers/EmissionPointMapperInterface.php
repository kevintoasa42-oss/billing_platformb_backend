<?php

namespace App\Context\V3\Modules\Core\Establishment\Domain\Mappers;

use App\Context\V3\Modules\Core\Establishment\Domain\Models\EmissionPoint;
use App\Context\V3\Modules\Core\Establishment\Infrastructure\Laravel\Eloquent\Models\EmissionPointModel;

interface EmissionPointMapperInterface
{
    public function toDomain(EmissionPointModel $record): EmissionPoint;

    /**
     * @param  iterable<EmissionPointModel>  $records
     * @return array<int, EmissionPoint>
     */
    public function toDomainList(iterable $records): array;

    /**
     * @return array<string, mixed>
     */
    public function toDatabaseArray(EmissionPoint $emissionPoint): array;
}
