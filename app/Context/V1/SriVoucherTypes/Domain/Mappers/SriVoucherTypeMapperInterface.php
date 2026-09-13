<?php

namespace App\Context\V1\SriVoucherTypes\Domain\Mappers;

use App\Context\V1\SriVoucherTypes\Domain\Models\SriVoucherType;

interface SriVoucherTypeMapperInterface
{
    public function toDomain(array $data): SriVoucherType;

    /** @return array<string, mixed> */
    public function toPersistence(SriVoucherType $voucherType): array;

    /** @return array<string, mixed> */
    public function toArray(SriVoucherType $voucherType): array;
}
