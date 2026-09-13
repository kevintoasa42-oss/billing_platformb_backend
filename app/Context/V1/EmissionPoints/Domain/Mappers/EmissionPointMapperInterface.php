<?php

namespace App\Context\V1\EmissionPoints\Domain\Mappers;

use App\Context\V1\EmissionPoints\Domain\Models\EmissionPoint;

interface EmissionPointMapperInterface
{
    public function toDomain(array $data): EmissionPoint;

    public function toPersistence(EmissionPoint $emissionPoint): array;

    public function toArray(EmissionPoint $emissionPoint): array;
}
