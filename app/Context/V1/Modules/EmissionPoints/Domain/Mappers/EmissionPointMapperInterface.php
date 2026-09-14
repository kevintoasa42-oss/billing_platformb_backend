<?php

namespace App\Context\V1\Modules\EmissionPoints\Domain\Mappers;

use App\Context\V1\Modules\EmissionPoints\Domain\Models\EmissionPoint;

interface EmissionPointMapperInterface
{
    public function toDomain(array $data): EmissionPoint;

    public function toPersistence(EmissionPoint $emissionPoint): array;

    public function toArray(EmissionPoint $emissionPoint): array;
}
