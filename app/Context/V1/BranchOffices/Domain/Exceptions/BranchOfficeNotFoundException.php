<?php

namespace App\Context\V1\BranchOffices\Domain\Exceptions;

use RuntimeException;

final class BranchOfficeNotFoundException extends RuntimeException
{
    public function __construct(int $id)
    {
        parent::__construct("Branch office {$id} was not found.");
    }
}
