<?php

namespace App\Context\V1\Partners\Domain\Exceptions;

use RuntimeException;

final class PartnerNotFoundException extends RuntimeException
{
    public function __construct(int $id)
    {
        parent::__construct("Partner {$id} was not found.");
    }
}
