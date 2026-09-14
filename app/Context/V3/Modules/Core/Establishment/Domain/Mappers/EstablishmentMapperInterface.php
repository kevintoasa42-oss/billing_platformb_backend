<?php

namespace App\Context\V3\Modules\Core\Establishment\Domain\Mappers;

use App\Context\V3\Modules\Core\Establishment\Domain\Models\Establishment;
use App\Context\V3\Modules\Core\Establishment\Infrastructure\Laravel\Eloquent\Models\EstablishmentModel;

interface EstablishmentMapperInterface
{
    public function toDomain(EstablishmentModel $record): Establishment;

    /**
     * @param  iterable<EstablishmentModel>  $records
     * @return array<int, Establishment>
     */
    public function toDomainList(iterable $records): array;

    /**
     * @return array<string, mixed>
     */
    public function toDatabaseArray(Establishment $establishment): array;
}
