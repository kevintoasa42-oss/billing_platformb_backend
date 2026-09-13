<?php

namespace App\Context\V1\SriVoucherTypes\Domain\Exceptions;

use RuntimeException;

final class SriVoucherTypeNotFoundException extends RuntimeException
{
    public function __construct(int|string $identifier)
    {
        parent::__construct("SRI voucher type [{$identifier}] was not found.");
    }
}
