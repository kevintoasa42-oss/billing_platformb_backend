<?php

namespace App\Context\V1\Modules\SriVoucherTypes\Application\Adapters;

use App\Context\V1\Modules\SriVoucherTypes\Application\DTOs\SriVoucherTypeDTO;

/** Stable application-facing catalogue contract for other bounded contexts. */
interface SriVoucherTypeCatalogInterface
{
    /** Returns the currently valid voucher type for its SRI document code. */
    public function currentByCode(string $code): ?SriVoucherTypeDTO;
}
