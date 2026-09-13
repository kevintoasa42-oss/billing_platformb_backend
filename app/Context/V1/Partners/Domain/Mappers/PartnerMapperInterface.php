<?php

namespace App\Context\V1\Partners\Domain\Mappers;

use App\Context\V1\Partners\Domain\Models\Partner;

interface PartnerMapperInterface
{
    /** @param array<string, mixed> $data */
    public function toDomain(array $data): Partner;

    /** @return array<string, mixed> */
    public function toPersistence(Partner $partner): array;

    /** @return array<string, mixed> */
    public function toArray(Partner $partner): array;
}
